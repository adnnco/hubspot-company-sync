<?php

namespace HubspotCompanySync\Interfaces;

interface InitRegister {

    /**
     * Registers the plugin hooks.
     *
     * @return void
     */
    public function register(): void;
}
