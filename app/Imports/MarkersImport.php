<?php

namespace App\Imports;

use App\Models\Marker;
use App\Models\Hazard;
use App\Models\Group;
use App\Models\TempData;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class MarkersImport implements ToCollection,WithHeadingRow,
//  ShouldQueue, WithChunkReading, WithBatchInserts,
WithValidation
{
    use Importable;

    protected $organizationId;

    /**
     * Constructor to initialize the organization ID.
     *
     * @param int $organizationId
     */
    public function __construct($organizationId)
    {
        $this->organizationId = $organizationId;
    }

    /**
     * Process the rows from the Excel file.
     *
     * @param Collection $rows
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            if ($this->isRowEmpty($row)) {
                continue;
            }
            $error = [];

            $hazardId = $this->getHazardId($row['marker_type'], $error);
            $groupId1 = $this->getGroupId($row['1st_group'], $error);
            $groupId2 = $this->getGroupId($row['2nd_group'], $error);
            $groupId3 = $this->getGroupId($row['3rd_group'], $error);
            $ids=array_filter([$groupId1,$groupId2,$groupId3]);
            $markerData = [
                'organization_id'=> $this->organizationId,
                'marker_title' => $row['marker_name'],
                'hazard_id' =>$hazardId,
                'hazard_name' => $row['marker_type'],
                'internal_id' => $row['internal_id'],
                'color' => $row['color'],
                'longitude' => $row['longitude'],
                'latitude' => $row['latitude'],
                'proximity' => $row['proximity'],
                'description' => $row['description'],
                'group_name_1' => $row['1st_group'],
                'group_name_2' => $row['2nd_group'],
                'group_name_3' => $row['3rd_group'],
                'group_id_1' => $groupId1,
                'group_id_2' => $groupId2,
                'group_id_3' => $groupId3,
                'status' => 0,
                'extra' => []
            ];
            if(count($error) == 0) {
                $markerData['status']= 1;
            }
            $existsInTempData = TempData::where('organization_id', $this->organizationId)
            ->where('marker_title', $row['marker_name'])
            ->where('longitude', $row['longitude'])
            ->where('latitude', $row['latitude'])
            ->exists();
        $existsInMarker = Marker::where('marker_title', $row['marker_name'])
            ->where('lat', $row['latitude'])
            ->where('long', $row['longitude']);
            if (count($ids)>0 && $ids[0]!=0) {
                $existsInMarker = $existsInMarker->whereIn('group_id',$ids);
            }
        $existsInMarker = $existsInMarker->exists();
        if ($existsInTempData) {
            $markerData['status'] = 0;
            $error[] = 'Duplicate entry found in TempData';
        }
        if ($existsInMarker) {
            $markerData['status'] = 0;
            $error[] = 'Duplicate entry found in Marker';
        }
            $errorJson = !empty($error) ? json_encode($error) : null;
            $markerData['extra'] = $errorJson;
            TempData::create($markerData);
        }
    }

    /**
     * Retrieve the hazard ID based on the name.
     *
     * @param string|null $hazardName
     * @return int|null
     */
    protected function getHazardId($hazardName, &$error = [])
    {
        if ($hazardName) {
            $value = str_replace([' ',], '-', strtolower($hazardName)); // Convert to lowercase
            $hazard = Hazard::whereRaw('LOWER(name) LIKE ?', ['%' . $value . '%'])
                ->select('id')
                ->first();

            if (!$hazard) {
                $error[] = "Hazard ID not found for marker type: " . $hazardName;
            }

            return $hazard ? $hazard->id : null;
        }

        $error[] = "Hazard name is empty or null.";
        return null;
    }

    /**
     * Retrieve the group ID based on the name.
     *
     * @param string|null $groupName
     * @return int|null
     */
    protected function getGroupId($groupName, &$error = [])
    {
        if ($groupName) {
            $groupName = strtolower($groupName);
            if ($groupName === "none") {
                return null; // "None" groups are ignored
            }
            if ($groupName === "all") {
                return 0; // "All" groups are represented by ID 0
            }
            $group = Group::whereRaw('LOWER(name) LIKE ?', ['%' . $groupName . '%'])
                ->where('organization_id', $this->organizationId)
                ->select('id')
                ->first();

            if (!$group) {
                $error[] = "Group ID not found for group: " . $groupName;
            }
            return $group ? $group->id : null;
        }
        $error[] = "Group name is empty or null.";
        return null;
    }

    protected function isRowEmpty($row)
    {
    // Columns to check for emptiness
    $columnsToCheck = ['marker_name','marker_type','internal_id','color','longitude','latitude','proximity','description','1st_group','2nd_group','3rd_group',];
    return empty(array_filter($columnsToCheck, function ($key) use ($row) {
        return isset($row[$key]) && !is_null($row[$key]) && $row[$key] !== '';
    }));
    }
    /**
     * Define the validation rules for the rows.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            // 'marker_name' => 'nullable|string',
            // 'internal_id' => 'nullable|string',
            // 'color' => 'nullable|string',
            // 'longitude' => 'required',
            // 'latitude' => 'required',
            // 'proximity' => 'nullable',
            // 'description' => 'nullable|string',
            // 'marker_type' => 'nullable|string',
            // 'group_id_1' => 'nullable|string',
            // 'group_id_2' => 'nullable|string',
            // 'group_id_3' => 'nullable|string',
        ];
    }

}
