<?php
// define the roles
define('ROLE_GUEST', 'guest');
define('ROLE_ADMIN', 'admin');
define('ROLE_STUDENT', 'student');
define('ROLE_TUTOR', 'tutor');

// define the permissions
$permissions = [
    ROLE_GUEST => [
        'home',
        'about_us',
        'our_tutor',
        'contact_us',
        'register'

    ],
    ROLE_ADMIN => [
        'admin_dashboard',
    ],
    ROLE_STUDENT => [
        'timetable',
        'booking'
    ],
    ROLE_TUTOR => [
        'timetable',
    ]
    
]

?>