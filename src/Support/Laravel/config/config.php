<?php

return array(

    'initialize' => function ($authorizable) {

        $user_model = Config::get('auth.model', 'User');

        $authenticated_user = $authorizable->getUser();

        // Any user can view user accounts.
        $authorizable->allow('show', $user_model);

        // Only anonymous users can create accounts.
        if (! $authenticated_user) {
            $authorizable->allow('create', $user_model);
        }

        // Authenticated users can update or delete their own accounts.
        $authorizable->allow(['update', 'destroy'], $user_model, function ($user) {
            // Within conditions, `$this` is the active Authorizable\Manager instance.
            return $this->getUser()->id == $user->id;
        });

    },

    // These messages can be translator keys or raw messages.
    // Translator keys will be looked up in the language file.
    'messages' => array(
        'access_denied' => array(
            'default' => 'messages.access_denied.resource.default',
        ),
    ),

);
