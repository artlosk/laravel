<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AdminMenuServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Регистрируем синглтон для меню
        $this->app->singleton('adminlte.menu', function () {
            return $this->buildSidebarMenu();
        });
    }

    public function boot()
    {
        // Логирование для отладки
        \Log::info('AdminMenuServiceProvider booted');
    }

    protected function buildSidebarMenu()
    {
        // Возвращаем меню, адаптированное из config/adminlte.php
        return [
            // Заголовок: MAIN NAVIGATION
            'MAIN NAVIGATION',

            // Пункт: Dashboard
            [
                'text' => 'Dashboard',
                'route' => 'backend.dashboard',
                'icon' => 'fas fa-tachometer-alt',
                'active' => ['backend.dashboard'],
                'can' => 'access-admin-panel',
                'order' => 10,
            ],

            // Пункт: Posts
            [
                'text' => 'Posts',
                'route' => 'backend.posts.index',
                'icon' => 'fas fa-file-alt',
                'active' => ['backend.posts.*'],
                'can' => 'read-posts',
                'order' => 20,
            ],

            // Пункт: Roles
            [
                'text' => 'Roles',
                'route' => 'backend.roles.index',
                'icon' => 'fas fa-user-shield',
                'active' => ['admin/roles'],
                'can' => 'manage-roles',
                'order' => 30,
            ],

            // Пункт: Permissions
            [
                'text' => 'Permissions',
                'route' => 'backend.permissions.index',
                'icon' => 'fas fa-key',
                'active' => ['backend.permissions.*'],
                'can' => 'manage-permissions',
                'order' => 40,
            ],

            // Пункт: User Roles & Permissions
            [
                'text' => 'User Roles & Permissions',
                'route' => 'backend.roles.manage',
                'icon' => 'fas fa-users-cog',
                'label_color' => 'success',
                'active' => ['backend.roles.manage', 'backend.roles.update-user', 'backend.roles.get-user'],
                'can' => ['manage-roles', 'manage-permissions'],
                'order' => 50,
            ],

            // Пункт: User Management (с подменю)
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

            // Заголовок: ACCOUNT SETTINGS
            'ACCOUNT SETTINGS',

            // Пункт: Profile
            [
                'text' => 'Profile',
                'route' => 'backend.profile',
                'icon' => 'fas fa-fw fa-user',
            ],

            // Пункт: Change Password
            [
                'text' => 'Change Password',
                'route' => 'backend.password',
                'icon' => 'fas fa-fw fa-lock',
            ],

            // Элемент навигационной панели: Поиск
            [
                'type' => 'navbar-search',
                'text' => 'search',
                'topnav_right' => true,
            ],

            // Элемент навигационной панели: Полноэкранный режим
            [
                'type' => 'fullscreen-widget',
                'topnav_right' => true,
            ],

            // Элемент боковой панели: Поиск
            [
                'type' => 'sidebar-menu-search',
                'text' => 'search',
            ],

            // Пункт: Blog
            [
                'text' => 'blog',
                'url' => 'admin/blog',
                'can' => 'manage-blog',
            ],

            // Пункт: Pages
            [
                'text' => 'pages',
                'url' => 'admin/pages',
                'icon' => 'far fa-fw fa-file',
                'label' => 4,
                'label_color' => 'success',
            ],

            // Заголовок: account_settings
            ['header' => 'account_settings'],

            // Пункт: Profile (повтор)
            [
                'text' => 'profile',
                'url' => 'admin/settings',
                'icon' => 'fas fa-fw fa-user',
            ],

            // Пункт: Change Password (повтор)
            [
                'text' => 'change_password',
                'url' => 'admin/settings',
                'icon' => 'fas fa-fw fa-lock',
            ],

            // Пункт: Multilevel (с многоуровневым подменю)
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

            // Заголовок: labels
            ['header' => 'labels'],

            // Пункт: Important
            [
                'text' => 'important',
                'icon_color' => 'red',
                'url' => '#',
            ],

            // Пункт: Warning
            [
                'text' => 'warning',
                'icon_color' => 'yellow',
                'url' => '#',
            ],

            // Пункт: Information
            [
                'text' => 'information',
                'icon_color' => 'cyan',
                'url' => '#',
            ],
        ];
    }
}
