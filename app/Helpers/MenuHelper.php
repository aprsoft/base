<?php

namespace App\Helpers;

use App\Helpers\Function\IconHelper;

class MenuHelper
{
    public static function getMainNavItems(): array
    {        
        return [
            [
                'icon' => 'home',
                'name' => 'Inicio',
                'path' => route('home', absolute: false),
            ],
         
        ];
    
    }

    public static function getAdministrationItems(): array
    {
        return [
            [
                'icon' => 'users',
                'name' => 'Usuarios',
                'subItems' => [
                    [
                        'name' => 'Listado',
                        'path' => route('users.index', absolute: false),
                        'permission' => 'users.index',
                        'pro' => false
                    ],
                    [
                        'name' => 'Crear Usuario',
                        'path' => route('users.create', absolute: false),
                        'permission' => 'users.create',
                        'pro' => false
                    ],
                ],
            ],        
           
        ];
    }

    public static function getRolesPermissionsItems(): array
    {
        return [
         
            [
                'icon' => 'access',
                'name' => 'Roles',
                'subItems' => [
                    [                        
                        'name' => 'listado',
                        'path' => route('roles.index', absolute: false),
                        'permission' => 'roles.index',
                        'pro'  => false
                    ],
                    [                        
                        'name' => 'Crear Roles',
                        'path' => route('roles.create', absolute: false),
                        'permission' => 'roles.create',
                        'pro'  => false
                    ],
                    
                ],
            ],
            [
                'icon' => 'access',
                'name' => 'Permisos',
                'subItems' => [
                    [                        
                        'name' => 'Listado',
                        'path' => route('permissions.index', absolute: false),
                        'permission' => 'permissions.index',
                        'pro'  => false
                    ],
                    [                        
                        'name' => 'Crear Permiso',
                        'path' => route('permissions.create', absolute: false),
                        'permission' => 'permissions.create',
                        'pro'  => false
                    ],
                    
                ],
            ]
        ];
    }    

    // public static function getSupportItems(): array
    // {
    //     return [            
    //         [
    //             'icon' => 'support',
    //             'name' => 'Errores',
    //              'path' => route('errors.index', absolute: false),
    //             'pro' => false
    //         ],
    //     ];
    // }

    // public static function getMenuGroups(): array
    // {   
             
    //     return [
    //         [
    //             'title' => 'Menu',
    //             'items' => self::getMainNavItems()
    //         ],
    //         [
    //             'title' => 'Administration',
    //             'items' => self::getAdministrationItems()
    //         ],
    //         [
    //             'title' => 'Roles y Permisos',
    //             'items' => self::getRolesPermissionsItems()
    //         ],
    //         [
    //             'title' => 'Soporte',
    //             'items' => self::getSupportItems()
    //         ]
    //     ];
    // }
    public static function getMenuGroups(): array
    {
        $user = auth('web')->user();

        if (!$user) {
            return [];
        }

        $groups = [
            [
                'title' => 'Menu',
                'items' => self::getMainNavItems(),
            ],
            [
                'title' => 'Administration',
                'items' => self::getAdministrationItems(),
            ],
            [
                'title' => 'Roles y Permisos',
                'items' => self::getRolesPermissionsItems(),
            ],
            // [
            //     'title' => 'Soporte',
            //     'items' => self::getSupportItems(),
            // ],
        ];

        foreach ($groups as &$group) {
            $items = [];

            foreach ($group['items'] as $item) {

                // Verificar permiso del elemento principal
                if (
                    isset($item['permission']) &&
                    !$user->can($item['permission'])
                ) {
                    continue;
                }

                // Filtrar elementos del submenú
                if (isset($item['subItems'])) {

                    $item['subItems'] = array_values(
                        array_filter(
                            $item['subItems'],
                            fn (array $subItem) =>
                                !isset($subItem['permission']) ||
                                $user->can($subItem['permission'])
                        )
                    );

                    // Ocultar menú padre sin opciones visibles
                    if (empty($item['subItems'])) {
                        continue;
                    }
                }

                $items[] = $item;
            }

            $group['items'] = $items;
        }

        unset($group);

        // Ocultar grupos que quedaron sin elementos
        return array_values(
            array_filter(
                $groups,
                fn (array $group) => !empty($group['items'])
            )
        );
    }

    public static function isActive($path): bool
    {
        return request()->is(ltrim($path, '/'));
    }
    
    public static function getIconSvg($iconName): string
    {
        return IconHelper::get($iconName);
    }
}
