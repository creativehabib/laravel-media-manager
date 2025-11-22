<?php
return [
    // কোন কোন ডিস্ক থেকে ফাইল ম্যানেজ করবে
    'disks' => [
        'public',
        's3',
        'do_spaces',
    ],

    // ডিফল্ট ডিস্ক
    'default_disk' => 'public',

    // রুট প্রিফিক্স
    'route_prefix' => 'admin/media',

    // মিডলওয়্যার (Botble-এর মত admin panel-এর জন্য)
    'middleware' => ['web', 'auth'],

    // Gate বা Permission name
    'permission' => 'manage_media',

    // প্রতি পেইজে ফাইল সংখ্যা
    'per_page' => 24,

    /* ----------------------------
   | Media Manager Toast Defaults
   |-----------------------------*/
    'toast' => [
        'position' => 'bottom-right', // top-left, top-right, bottom-left, bottom-right
        'timeout'  => 3000,           // milliseconds
        'max'      => 4,              // queue length
    ],
];
