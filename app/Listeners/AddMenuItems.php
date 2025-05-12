<?php

namespace App\Listeners;

use JeroenNoten\LaravelAdminLte\Events\BuildingMenu;

class AddMenuItems
{
    public function handle(BuildingMenu $event)
    {
        // Получаем меню из синглтона
        $menu = app('adminlte.menu');
        // Добавляем элементы меню
        $event->menu->add(...$menu);
    }
}
