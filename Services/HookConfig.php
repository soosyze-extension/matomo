<?php

namespace SoosyzeExtension\Matomo\Services;

class HookConfig
{
    /**
     * @var \Soosyze\Config
     */
    protected $config;

    /**
     * @var \SoosyzeCore\User\Services\User
     */
    protected $user;

    public function __construct($config, $user)
    {
        $this->config = $config;
        $this->user   = $user;
    }

    public function menu(&$menu)
    {
        $menu[] = [
            'key'        => 'matomo',
            'title_link' => 'Matomo'
        ];
    }

    public function form(&$form, $data)
    {
        return $form->group('config-fieldset', 'fieldset', function ($form) use ($data) {
            $form->legend('config-legend', 'Paramètres')
                    ->group('analytics_id-group', 'div', function ($form) use ($data) {
                        $form->label('analytics_id-label', 'Matomo site ID')
                        ->text('analytics_id', [
                            'class'       => 'form-control',
                            'required'    => 1,
                            'placeholder' => 1,
                            'value'       => $data[ 'analytics_id' ]
                        ]);
                    }, [ 'class' => 'form-group' ])
                    ->group('analytics_url-group', 'div', function ($form) use ($data) {
                        $form->label('analytics_url-label', 'Matomo URL')
                        ->text('analytics_url', [
                            'class'       => 'form-control',
                            'required'    => 1,
                            'placeholder' => 'https://matomo.example.com/',
                            'value'       => $data[ 'analytics_url' ]
                        ]);
                    }, [ 'class' => 'form-group' ]);
        })
                ->group('page-fieldset', 'fieldset', function ($form) use ($data) {
                    $form->legend('page-legend', 'Pages')
                    ->group('visibility-group', 'div', function ($form) use ($data) {
                        $form->radio('analytics_visibility_pages', [
                            'checked'  => !$data[ 'analytics_visibility_pages' ],
                            'required' => 1,
                            'value'    => 0,
                            'id'       => 'visibility1'
                        ])->label('analytics_visibility_pages-label', 'Exclure le suivi des pages listées', [
                            'for' => 'visibility1'
                        ]);
                    }, [ 'class' => 'form-group' ])
                    ->group('visibility1-group', 'div', function ($form) use ($data) {
                        $form->radio('analytics_visibility_pages', [
                            'checked'  => $data[ 'analytics_visibility_pages' ],
                            'required' => 1,
                            'value'    => 1,
                            'id'       => 'visibility2'
                        ])->label('analytics_visibility_pages-label', 'Activer le suivi des pages listées', [
                            'for' => 'visibility2'
                        ]);
                    }, [ 'class' => 'form-group' ])
                    ->group('url-group', 'div', function ($form) use ($data) {
                        $form->label('url-label', 'Liste des pages', [
                            'data-tooltip' => 'Saisir un chemin par ligne. Le caractère «%» est un caractère de remplacement spécifiant tous les caractères.'
                        ])
                        ->textarea('analytics_pages', $data[ 'analytics_pages' ], [
                            'class'       => 'form-control',
                            'placeholder' => 'admin' . PHP_EOL . 'admin/*',
                            'rows'        => 5
                        ]);
                    }, [ 'class' => 'form-group' ]);
                })
                ->group('roles-fieldset', 'fieldset', function ($form) use ($data) {
                    $form->legend('role-legend', 'Rôles')
                    ->group('visibility-group', 'div', function ($form) use ($data) {
                        $form->radio('analytics_visibility_roles', [
                            'checked'  => !$data[ 'analytics_visibility_roles' ],
                            'id'       => 'visibility3',
                            'required' => 1,
                            'value'    => 0
                        ])->label('analytics_visibility_roles-label', 'Activer le suivi aux rôles sélectionnés', [
                            'for' => 'visibility3'
                        ]);
                    }, [ 'class' => 'form-group' ])
                    ->group('visibility1-group', 'div', function ($form) use ($data) {
                        $form->radio('analytics_visibility_roles', [
                            'checked'  => $data[ 'analytics_visibility_roles' ],
                            'id'       => 'visibility4',
                            'required' => 1,
                            'value'    => 1
                        ])->label('analytics_visibility_roles-label', 'Activer le suivi aux rôles non sélectionnés', [
                            'for' => 'visibility4'
                        ]);
                    }, [ 'class' => 'form-group' ]);
                    $data[ 'analytics_roles' ] = explode(',', $data[ 'analytics_roles' ]);
                    foreach ($this->user->getRoles() as $role) {
                        $form->group("analytics_roles-{$role[ 'role_id' ]}-group", 'div', function ($form) use ($data, $role) {
                            $form->checkbox("analytics_roles-{$role[ 'role_id' ]}", [
                                'value'   => $role[ 'role_id' ],
                                'checked' => \in_array($role[ 'role_id' ], $data[ 'analytics_roles' ])
                            ])
                            ->label('analytics_roles-label', '<span class="ui"></span>' . $role[ 'role_label' ], [
                                'for' => "analytics_roles-{$role[ 'role_id' ]}"
                            ]);
                        }, [ 'class' => 'form-group' ]);
                    }
                })
                ->token('config_matomo')
                ->submit('submit', 'Enregistrer', [ 'class' => 'btn btn-success' ]);
    }

    public function validator(&$validator)
    {
        $validator->setRules([
            'analytics_id'               => 'required|string',
            'analytics_url'              => 'required|url|htmlsc',
            'analytics_visibility_pages' => 'bool',
            'analytics_pages'            => 'required|string|htmlsc',
            'analytics_visibility_roles' => 'bool'
        ]);

        foreach ($this->user->getRoles() as $role) {
            $validator->addRule("analytics_roles-{$role[ 'role_id' ]}", 'string');
        }
    }

    public function before(
        \Soosyze\Components\Validator\Validator &$validator,
        &$data
    ) {
        $analytics_roles = [];
        foreach ($this->user->getRoles() as $role) {
            if ($validator->getInput("analytics_roles-{$role[ 'role_id' ]}")) {
                $analytics_roles[] = $role[ 'role_id' ];
            }
        }

        $data = [
            'analytics_id'               => $validator->getInput('analytics_id'),
            'analytics_url'              => trim($validator->getInput('analytics_url'), '/\\') . '/',
            'analytics_visibility_pages' => ($validator->getInput('analytics_visibility_pages') === '1'),
            'analytics_pages'            => $validator->getInput('analytics_pages'),
            'analytics_visibility_roles' => ($validator->getInput('analytics_visibility_roles') === '1'),
            'analytics_roles'            => implode(',', $analytics_roles)
        ];
    }
}
