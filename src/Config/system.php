<?php
return [

    /**
     * AWS for Customization.
     */
    [
        'key'  => 'customization_aws',
        'name' => 'Customization Aws',
        'info' => 'Manage your aws setting for customization',
        'sort' => 8,
    ], [
        'key'  => 'customization_aws.customization_s3_bucket',
        'name' => 'S3 Bucket for Customization',
        'info' => 'S3 Bucket information for customization',
        'icon' => 'settings/store.svg',
        'sort' => 1,
    ], [
        'key'    => 'customization_aws.customization_s3_bucket.customization_setting',
        'name'   => 'Customization AWS S3 Bucket Setting',
        'info'   => '',
        'sort'   => 1,
        'fields' => [
            [
                'name'          => 'customization_s3_enabled',
                'title'         => 'Enabled',
                'type'          => 'boolean',
                'default'       => 0,
                'locale_based'  => true,
                'channel_based' => true,
            ], [
                'name'    => 'customization_access_key',
                'title'   => 'Access Key',
                'type'    => 'text',
                'default' => '',
            ], [
                'name'    => 'customization_secret_key',
                'title'   => 'Secret Key',
                'type'    => 'text',
                'default' => '',
            ],[
                'name'    => 'customization_default_region',
                'title'   => 'Default Region',
                'type'    => 'text',
                'default' => '',
            ],[
                'name'    => 'customization_bucket_name',
                'title'   => 'Bucket Name',
                'type'    => 'text',
                'default' => '',
            ],[
                'name'    => 'customization_console_url',
                'title'   => 'Console Url',
                'type'    => 'text',
                'default' => '',
            ],[
                'name'    => 'customization_aws_url',
                'title'   => 'Aws Url',
                'type'    => 'text',
                'default' => '',
            ],
        ],
    ],


];