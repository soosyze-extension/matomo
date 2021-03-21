<?php

namespace SoosyzeExtension\Matomo\Hook;

class Config implements \SoosyzeCore\Config\ConfigInterface
{
    const ENABLE_TRACKING_PAGES = 1;

    const ENABLE_TRACKING_ROLES = 1;

    const EXCLUDE_TRACKING_PAGES = 0;

    const EXCLUDE_TRACKING_ROLES = 0;

    private static $attrGrp = [ 'class' => 'form-group' ];

    /**
     * @var \SoosyzeCore\User\Services\User
     */
    private $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function after(&$validator, array $data, $id)
    {
    }

    public function before(&$validator, array &$data, $id)
    {
        $analyticsRoles = [];
        foreach ($this->user->getRoles() as $role) {
            if ($validator->getInput("analytics_roles-{$role[ 'role_id' ]}")) {
                $analyticsRoles[] = $role[ 'role_id' ];
            }
        }

        $data = [
            'analytics_matomo'           => (bool) $validator->getInput('analytics_matomo'),
            'analytics_id'               => $validator->getInput('analytics_id'),
            'analytics_url'              => trim($validator->getInput('analytics_url'), '/\\') . '/',
            'analytics_visibility_pages' => (bool) $validator->getInput('analytics_visibility_pages'),
            'analytics_pages'            => $validator->getInput('analytics_pages'),
            'analytics_visibility_roles' => (bool) $validator->getInput('analytics_visibility_roles'),
            'analytics_roles'            => implode(',', $analyticsRoles)
        ];
    }

    public function defaultValues()
    {
        return [
            'analytics_matomo'           => false,
            'analytics_id'               => '',
            'analytics_url'              => '',
            'analytics_visibility_pages' => self::EXCLUDE_TRACKING_PAGES,
            'analytics_pages'            => '',
            'analytics_visibility_roles' => self::EXCLUDE_TRACKING_ROLES,
            'analytics_roles'            => ''
        ];
    }

    public function files(array &$inputsFile)
    {
    }

    public function form(&$form, array $data, $req)
    {
        return $form->group('config-fieldset', 'fieldset', function ($form) use ($data) {
            $form->legend('config-legend', t('Settings'))
                    ->group('analytics_matomo-group', 'div', function ($form) use ($data) {
                        $form->checkbox('analytics_matomo', [
                            'checked' => $data[ 'analytics_matomo' ]
                        ])
                        ->label('analytics_matomo-label', '<span class="ui"></span>' . t('Activer matamo'), [
                            'for' => 'analytics_matomo'
                        ]);
                    }, self::$attrGrp)
                    ->group('analytics_id-group', 'div', function ($form) use ($data) {
                        $form->label('analytics_id-label', 'Matomo site ID')
                        ->text('analytics_id', [
                            'class'       => 'form-control',
                            'placeholder' => 1,
                            'required'    => 1,
                            'value'       => $data[ 'analytics_id' ]
                        ]);
                    }, self::$attrGrp)
                    ->group('analytics_url-group', 'div', function ($form) use ($data) {
                        $form->label('analytics_url-label', 'Matomo URL')
                        ->text('analytics_url', [
                            'class'       => 'form-control',
                            'placeholder' => 'https://matomo.example.com/',
                            'required'    => 1,
                            'value'       => $data[ 'analytics_url' ]
                        ]);
                    }, self::$attrGrp);
        })
                ->group('page-fieldset', 'fieldset', function ($form) use ($data) {
                    $form->legend('page-legend', t('Pages'))
                    ->group('visibility-group', 'div', function ($form) use ($data) {
                        $form->radio('analytics_visibility_pages', [
                            'checked'  => !$data[ 'analytics_visibility_pages' ],
                            'id'       => 'visibility1',
                            'required' => 1,
                            'value'    => self::EXCLUDE_TRACKING_PAGES
                        ])->label('analytics_visibility_pages-label', '<i class="fa fa-eye-slash" aria-hidden="true"></i> ' . t('Exclude tracking of listed pages'), [
                            'for' => 'visibility1'
                        ]);
                    }, self::$attrGrp)
                    ->group('visibility1-group', 'div', function ($form) use ($data) {
                        $form->radio('analytics_visibility_pages', [
                            'checked'  => $data[ 'analytics_visibility_pages' ],
                            'id'       => 'visibility2',
                            'required' => 1,
                            'value'    => self::ENABLE_TRACKING_PAGES
                        ])->label('analytics_visibility_pages-label', '<i class="fa fa-eye" aria-hidden="true"></i> ' . t('Enable tracking of listed pages'), [
                            'for' => 'visibility2'
                        ]);
                    }, self::$attrGrp)
                    ->group('analytics_pages-group', 'div', function ($form) use ($data) {
                        $form->label('analytics_pages-label', t('List of pages'), [
                            'data-tooltip' => t('Entrez un chemin par ligne. Le caractère "%" est un caractère générique qui spécifie tous les caractères.')
                        ])
                        ->textarea('analytics_pages', $data[ 'analytics_pages' ], [
                            'class'       => 'form-control',
                            'placeholder' => 'admin' . PHP_EOL . 'admin/*',
                            'rows'        => 5
                        ])
                        ->html('info-variable_allowed', '<p>:content</p>', [
                            '_content' => t('Variables allowed') . ' <code>%</code>'
                        ]);
                    }, self::$attrGrp);
                })
                ->group('roles-fieldset', 'fieldset', function ($form) use ($data) {
                    $form->legend('role-legend', t('User Roles'))
                    ->group('visibility-group', 'div', function ($form) use ($data) {
                        $form->radio('analytics_visibility_roles', [
                            'checked'  => !$data[ 'analytics_visibility_roles' ],
                            'id'       => 'visibility3',
                            'required' => 1,
                            'value'    => 0
                        ])->label('analytics_visibility_roles-label', '<i class="fa fa-eye-slash" aria-hidden="true"></i> ' . t('Enable tracking on selected roles'), [
                            'for' => 'visibility3'
                        ]);
                    }, self::$attrGrp)
                    ->group('visibility1-group', 'div', function ($form) use ($data) {
                        $form->radio('analytics_visibility_roles', [
                            'checked'  => $data[ 'analytics_visibility_roles' ],
                            'id'       => 'visibility4',
                            'required' => 1,
                            'value'    => 1
                        ])->label('analytics_visibility_roles-label', '<i class="fa fa-eye" aria-hidden="true"></i> ' . t('Enable tracking for unselected roles'), [
                            'for' => 'visibility4'
                        ]);
                    }, self::$attrGrp);
                    $data[ 'analytics_roles' ] = explode(',', $data[ 'analytics_roles' ]);
                    foreach ($this->user->getRoles() as $role) {
                        $form->group("analytics_roles-{$role[ 'role_id' ]}-group", 'div', function ($form) use ($data, $role) {
                            $form->checkbox("analytics_roles-{$role[ 'role_id' ]}", [
                                'checked' => \in_array($role[ 'role_id' ], $data[ 'analytics_roles' ]),
                                'value'   => $role[ 'role_id' ]
                            ])
                            ->label('analytics_roles-label', '<span class="ui"></span>' . t($role[ 'role_label' ]), [
                                'for' => "analytics_roles-{$role[ 'role_id' ]}"
                            ]);
                        }, self::$attrGrp);
                    }
                })
                ->token('config_matomo')
                ->submit('submit', t('Save'), [ 'class' => 'btn btn-success' ]);
    }

    public function menu(array &$menu)
    {
        $menu[ 'matomo' ] = [
            'title_link' => 'Matomo'
        ];
    }

    public function validator(&$validator)
    {
        $validator->setRules([
            'analytics_matomo'           => 'bool',
            'analytics_id'               => 'required|string',
            'analytics_url'              => 'required|url',
            'analytics_visibility_pages' => 'bool',
            'analytics_pages'            => '!required|string',
            'analytics_visibility_roles' => 'bool'
        ]);

        foreach ($this->user->getRoles() as $role) {
            $validator->addRule("analytics_roles-{$role[ 'role_id' ]}", 'string');
        }
    }
}
