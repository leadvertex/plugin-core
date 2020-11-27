<?php
/**
 * Created for plugin-core
 * Date: 26.11.2020
 * @author Timur Kasumov (XAKEPEHOK)
 */

namespace Leadvertex\Plugin\Components\Core\Actions\Settings;


use Leadvertex\Plugin\Components\Settings\SettingsForm;
use Leadvertex\Plugin\Components\Core\Actions\FormAction;

class GetSettingsFormAction extends FormAction
{

    public function __construct()
    {
        parent::__construct(SettingsForm::getInstance());
    }

}