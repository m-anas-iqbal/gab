<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        // 'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
        'webhook_secret' => 'whsec_xtIyUPuYa2pdd3i1tWecfRCqWznHiJTG',
    ],
    'fcm' =>  [
            'file' => storage_path(env('FCM_JSON','app/json/file.json')),
            "type"=> "service_account",
            "project_id"=>"efms-38b12",
            "private_key_id"=>  "1645facd0e0b74c0bf405956e552ba30b341d3c8",
            "private_key"=> "-----BEGIN PRIVATE KEY-----\nMIIEvAIBADANBgkqhkiG9w0BAQEFAASCBKYwggSiAgEAAoIBAQDjrtDKWQx7gIUG\naYNlp1PUd6ZEpj7PDCPLHIHd6Iv6jR97f0HwFozHHvFY1w7txTXVK9rpIuvOtcW+\nTfRqPKG6m8avcwjOUHCnQgThGOJ/0dwNyI8p2wZgjvn8+lryTmO2H9UUkCrkCjrO\n0Bb2KeHIipPQMEZv/W+qHgy9i/Qp19MjChaf/vdGHL/iJWdyeclM2TEgkcYH7tCH\nGv6dwZTYoKmnCI4g8OOpsifa8vqqNQlgKwB7qpU8N2nEWfZKgTihU5Y2pPxjMR96\nW/Xvj37FvyxHi9MWoGpqXsgjNXLhYEKtmW2QKjHJgFRP+i8UPYg/yJMiYT+vR1KC\nz44O/mAtAgMBAAECggEACThlICynAsFbobzYI1+5V1tZEmKhHIVjVbBb2f623Him\nk7u9kdl37gBkybI72okn+vABt3tAjWhzD5fclAjMi+APFb/U1XYQedWEmREG6yI2\nhSBgG9PQ2Ewx93q0ACgIhbGYUXLdKcr4QrPOmj1r9VEXV1D9KT/cK8l3vp1ST/dn\n8xW46ZSfUy3SygjZb1Mo48et95q0k6jL5szI6IvkTyOfZfKc2AUsoKOLsWZ2C04n\naxR4DVgz5r10oKEVcUHsFdZTDJWvM644ejfIwxPC2VQSpNPo0oyx58eoQlyQejbM\nbImnw+ZBrREiwfume8OvHH3vpz9F4Qxs646VSv55AQKBgQD+Hwn6SaF0O/3z+Agu\nGSFdZ2CM04XcvnT4Sfx5xIPvELI+IW7Fmun5u0ToSl7dq1jYa8KskXzQgzpJ6y6r\noXfx0Mi23ZXwmxYys+GbqIEv4DwZwkke6NIJsNxIi2qCbqjIgVH0bIRkR2Omif0T\nGjTT+ZEjnLqbyQg2TZPJ0JuboQKBgQDlXbz532F5zHgesHePX3YcIZsyvgfXsko8\nmBwKBn+8QLQbgMbZcGsq9lHSecyw/CPi5cG7Vf6ToYPXTbfrtmikBDRcRR4dNJRy\nbI2+IAlMq1ryy58k/Z+l75GKx7/cshAoP17Jb3YxgRGzES/NIV9unVBOzhkfuH73\n8P0uAKzZDQKBgANZpWgHUHmX0OFGg9UXv7jbhApXP4yJdkFPuGRiktqz/aWUC0HP\nqQYB0ga99EI34BP6V7autFaLZFlVIGhi2JH3jq3aff+OC9zfQjorHojjC+fLB+vA\nZgR6sGzEacMOcmsySUJPq+8mgcnQR+XYkpm40JEHn+4t2E3e9/PRLDwBAoGAMm22\no9V6L/ZFnrG7x7j8VdkJr1FalhDsA4CKAaZGJVSwhK9+iMYPueVoPfdriqoVFcjg\nHFke3MSRISBERL3ZKd6GyJaltVQfIz08uMWAZevy1hjwx4g/tpMCk9mxFEvOA5tT\nMsSf3uh6xL0NnnMs8TwDMSBdI71DpS3F7HxXhG0CgYBWvSDC5f0U8IXNRY09zYKX\npNQ8F3XJFt3WmAHHBlCEpeFK+k4mXVupQGGlik79ksX/DR74Ju0meo+VGRECOn2v\nAjoS4jrqgzg/9c7b3tmq7cMTdEUe3Vg6xSiQCoSLt2r6Y9ZJshKoUe0oNA/I00Dm\n7C6K0BsB9JOifiORwBfwDg==\n-----END PRIVATE KEY-----\n",
            "client_email"=> "firebase-adminsdk-kdsbt@hrms-27914.iam.gserviceaccount.com",
            "client_id"=> "105849161577440606910",
            "auth_uri"=> "https://accounts.google.com/o/oauth2/auth",
            "token_uri"=> "https://oauth2.googleapis.com/token",
            "auth_provider_x509_cert_url"=>  "https://www.googleapis.com/oauth2/v1/certs",
            "client_x509_cert_url"=> "https://www.googleapis.com/robot/v1/metadata/x509/firebase-adminsdk-4t0sn%40efms-38b12.iam.gserviceaccount.com",
            "universe_domain"=> "googleapis.com"
        ],

];
