<?php namespace ImpulseTechnologies\Campaignr\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

/**
 * Registrations Backend Controller
 */
class Registrations extends Controller
{
    public $implement = [
        'Backend\Behaviors\ListController',
        'Backend\Behaviors\FormController',
        'Backend\Behaviors\ImportExportController'
    ];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $importExportConfig = 'config_import_export.yaml';

    public $requiredPermissions = ['campaignr.registrations.*'];

    /**
     * @var string HTML body tag class
     */
    public $bodyClass = 'compact-container';

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('impulsetechnologies.campaignr', 'main-menu-item', 'registrations');
    }

    /**
     * Extend the list query to include relationships
     */
    public function listExtendQuery($query)
    {
        $query->with('event');
    }
}
