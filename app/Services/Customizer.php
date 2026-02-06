<?php

namespace App\Services;

class Customizer
{
    protected $sections = [];
    protected $controls = [];

    /**
     * Add a new section to the customizer.
     *
     * @param string $id Unique ID for the section
     * @param array $args ['title' => 'My Section', 'priority' => 30]
     */
    public function addSection($id, $args)
    {
        $this->sections[$id] = array_merge([
            'id' => $id,
            'title' => 'Untitled Section',
            'priority' => 100,
            'description' => ''
        ], $args);
    }

    /**
     * Add a control to a section.
     *
     * @param string $id Unique ID for the control (usually matches setting key)
     * @param array $args ['label' => 'Color', 'section' => 'colors', 'type' => 'text']
     */
    public function addControl($id, $args)
    {
        // Default arguments
        $defaults = [
            'id' => $id,
            'label' => 'Untitled Control',
            'section' => 'default',
            'type' => 'text', // text, textarea, select, color, checkbox
            'priority' => 10,
            'choices' => [], // For select
            'default' => '',
            'description' => ''
        ];

        $this->controls[$id] = array_merge($defaults, $args);
    }

    /**
     * Get all sections sorted by priority.
     */
    public function getSections()
    {
        uasort($this->sections, function ($a, $b) {
            return $a['priority'] <=> $b['priority'];
        });
        return $this->sections;
    }

    /**
     * Get controls for a specific section sorted by priority.
     */
    public function getControls($sectionId)
    {
        $controls = array_filter($this->controls, function ($control) use ($sectionId) {
            return $control['section'] === $sectionId;
        });

        uasort($controls, function ($a, $b) {
            return $a['priority'] <=> $b['priority'];
        });

        return $controls;
    }
}
