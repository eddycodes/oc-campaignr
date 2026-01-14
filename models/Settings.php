<?php namespace ImpulseTechnologies\Campaignr\Models;

use Model;

class Settings extends Model
{
    public $implement = ['System.Behaviors.SettingsModel'];

    // A unique code
    public $settingsCode = 'impulsetechnologies_campaignr_settings';

    // Reference to field configuration
    public $settingsFields = '$/impulsetechnologies/campaignr/models/settings/fields.yaml';

    /**
     * Initialize default values
     */
    public function initSettingsData()
    {
        // Event List Component defaults
        $this->event_primary_color = '#003866';
        $this->event_secondary_color = '#0065b8';
        $this->event_white = '#ffffff';
        $this->event_text_primary = '#2d3748';
        $this->event_text_secondary = '#495057';
        $this->event_text_muted = '#6c757d';
        $this->event_bg_light = '#f8f9fa';
        $this->event_border_color = '#e9ecef';

        // Event Component defaults
        $this->event_component_primary = '#007bff';
        $this->event_component_primary_hover = '#0056b3';
        $this->event_component_success = '#28a745';
        $this->event_component_secondary = '#6c757d';
        $this->event_component_text_primary = '#212529';
        $this->event_component_text_secondary = '#495057';
        $this->event_component_text_muted = '#6c757d';
        $this->event_component_bg_light = '#f8f9fa';
        $this->event_component_bg_info = '#e7f3ff';
        $this->event_component_border = '#e9ecef';
        $this->event_component_border_light = '#dee2e6';
        $this->event_component_white = '#ffffff';
        $this->event_component_info_text = '#004085';
    }

    /**
     * Generate CSS with custom colors
     */
    public static function getCssVariables()
    {
        $settings = self::instance();

        $css = ":root {\n";

        // Event List Component variables
        $css .= "    --event-primary-color: {$settings->event_primary_color};\n";
        $css .= "    --event-secondary-color: {$settings->event_secondary_color};\n";
        $css .= "    --event-white: {$settings->event_white};\n";
        $css .= "    --event-text-primary: {$settings->event_text_primary};\n";
        $css .= "    --event-text-secondary: {$settings->event_text_secondary};\n";
        $css .= "    --event-text-muted: {$settings->event_text_muted};\n";
        $css .= "    --event-bg-light: {$settings->event_bg_light};\n";
        $css .= "    --event-border-color: {$settings->event_border_color};\n";

        // Event Component variables
        $css .= "    --event-component-primary: {$settings->event_component_primary};\n";
        $css .= "    --event-component-primary-hover: {$settings->event_component_primary_hover};\n";
        $css .= "    --event-component-success: {$settings->event_component_success};\n";
        $css .= "    --event-component-secondary: {$settings->event_component_secondary};\n";
        $css .= "    --event-component-text-primary: {$settings->event_component_text_primary};\n";
        $css .= "    --event-component-text-secondary: {$settings->event_component_text_secondary};\n";
        $css .= "    --event-component-text-muted: {$settings->event_component_text_muted};\n";
        $css .= "    --event-component-bg-light: {$settings->event_component_bg_light};\n";
        $css .= "    --event-component-bg-info: {$settings->event_component_bg_info};\n";
        $css .= "    --event-component-border: {$settings->event_component_border};\n";
        $css .= "    --event-component-border-light: {$settings->event_component_border_light};\n";
        $css .= "    --event-component-white: {$settings->event_component_white};\n";
        $css .= "    --event-component-info-text: {$settings->event_component_info_text};\n";

        $css .= "}\n";

        return $css;
    }
}
