<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AdminMenuServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('adminlte.menu', function () {
            return $this->buildSidebarMenu();
        });
    }

    public function boot()
    {
    }

    protected function buildSidebarMenu()
    {
        return [
            'MAIN NAVIGATION',

            [
                'text' => 'Dashboard',
                'route' => 'backend.dashboard',
                'icon' => 'fas fa-tachometer-alt',
                'active' => ['backend.dashboard'],
                'can' => 'access-admin-panel',
                'order' => 10,
            ],

            [
                'text' => 'Posts',
                'route' => 'backend.posts.index',
                'icon' => 'fas fa-file-alt',
                'active' => ['backend.posts.*'],
                'can' => 'read-posts',
                'order' => 20,
            ],

            [
                'text' => 'Roles',
                'route' => 'backend.roles.index',
                'icon' => 'fas fa-user-shield',
                'active' => ['admin/roles'],
                'can' => 'manage-roles',
                'order' => 30,
            ],

            [
                'text' => 'Permissions',
                'route' => 'backend.permissions.index',
                'icon' => 'fas fa-key',
                'active' => ['backend.permissions.*'],
                'can' => 'manage-permissions',
                'order' => 40,
            ],

            [
                'text' => 'User Roles & Permissions',
                'route' => 'backend.roles.manage',
                'icon' => 'fas fa-users-cog',
                'label_color' => 'success',
                'active' => ['backend.roles.manage', 'backend.roles.update-user', 'backend.roles.get-user'],
                'can' => ['manage-roles', 'manage-permissions'],
                'order' => 50,
            ],

            [
                'text' => 'User Management',
                'icon' => 'fas fa-users',
                'label' => \App\Models\User::count(),
                'active' => ['backend.users*'],
                'can' => 'manage-users',
                'submenu' => [
                    [
                        'text' => 'List Users',
                        'url' => '/admin/users',
                        'icon' => 'fas fa-list',
                        'active' => ['admin/users'],
                    ],
                    [
                        'text' => 'Create User',
                        'url' => '/admin/users/create',
                        'icon' => 'fas fa-plus',
                        'active' => ['admin/users/create'],
                    ],
                ],
                'order' => 60,
            ],

            'ACCOUNT SETTINGS',

            [
                'text' => 'Profile',
                'route' => 'backend.profile',
                'icon' => 'fas fa-fw fa-user',
            ],

            [
                'text' => 'Change Password',
                'route' => 'backend.password',
                'icon' => 'fas fa-fw fa-lock',
            ],

            [
                'type' => 'navbar-search',
                'text' => 'search',
                'topnav_right' => true,
            ],

            [
                'type' => 'fullscreen-widget',
                'topnav_right' => true,
            ],

            [
                'type' => 'sidebar-menu-search',
                'text' => 'search',
            ],

            [
                'text' => 'blog',
                'url' => 'admin/blog',
                'can' => 'manage-blog',
            ],

            [
                'text' => 'pages',
                'url' => 'admin/pages',
                'icon' => 'far fa-fw fa-file',
                'label' => 4,
                'label_color' => 'success',
            ],

            ['header' => 'account_settings'],

            [
                'text' => 'profile',
                'url' => 'admin/settings',
                'icon' => 'fas fa-fw fa-user',
            ],

            [
                'text' => 'change_password',
                'url' => 'admin/settings',
                'icon' => 'fas fa-fw fa-lock',
            ],

            [
                'text' => 'multilevel',
                'icon' => 'fas fa-fw fa-share',
                'submenu' => [
                    [
                        'text' => 'level_one',
                        'url' => '#',
                    ],
                    [
                        'text' => 'level_one',
                        'url' => '#',
                        'submenu' => [
                            [
                                'text' => 'level_two',
                                'url' => '#',
                            ],
                            [
                                'text' => 'level_two',
                                'url' => '#',
                                'submenu' => [
                                    [
                                        'text' => 'level_three',
                                        'url' => '#',
                                    ],
                                    [
                                        'text' => 'level_three',
                                        'url' => '#',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        'text' => 'level_one',
                        'url' => '#',
                    ],
                ],
            ],

            ['header' => 'labels'],

            [
                'text' => 'important',
                'icon_color' => 'red',
                'url' => '#',
            ],

            [
                'text' => 'warning',
                'icon_color' => 'yellow',
                'url' => '#',
            ],

            [
                'text' => 'information',
                'icon_color' => 'cyan',
                'url' => '#',
            ],
        ];
    }
}
