<?php

return array(

    'initialize' => function ($authorizable) {

        $user_model = Config::get('auth.model', 'User');

        $authenticated_user = $authorizable->getUser();

        // Anyone can view user accounts.
        $authorizable->allow('show', $user_model);

        // Only anonymous users can create accounts.
        if (! $authenticated_user) {
            $authorizable->allow('create', $user_model);
        }

        // Users can update or delete their own accounts.
        $authorizable->allow(['update', 'delete'], $user_model, function ($user) {
            return $this->getUser() == $user->id;
        });

    }

);
