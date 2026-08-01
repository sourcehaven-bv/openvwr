<?php

declare(strict_types=1);

// Points of attention for a DPIA. These are recommendations, not blockers: the
// person filling it in decides when the DPIA is finished. The texts therefore
// state what is missing and why that matters, without asserting that something
// is wrong.
return [
    'paragraph' => 'Section :paragraph',
    'unnamed' => 'without description',

    'heading' => 'Points of attention',
    'save_heading' => 'There are points of attention',
    'save_description' => 'The DPIA can simply be saved. These points are worth reviewing before you have the DPIA adopted.',
    'save_anyway' => 'Save anyway',
    'back_to_form' => 'Back to the form',
    'none' => 'No points of attention have been found at this time.',
    'count' => '{1} 1 point of attention|[2,*] :count points of attention',
    'section_notice' => 'Please note in this section:',
    'section_risks_without_measure' => "These risks are not yet linked to a measure:",
    'section_measures_without_risk' => "These measures do not yet address any risk:",
    'section_high_residual_risk' => 'A high residual risk remains. If you cannot reduce it, consult the Dutch Data Protection Authority (Autoriteit Persoonsgegevens) before the processing begins (Article 36 GDPR).',
    'and_more' => '{1} And 1 other point of attention.|[2,*] And :count other points of attention.',

    'personal_data_without_exception_ground' =>
        'For ":gegeven" the type ":type" has been chosen. Such data may in principle not be processed; enter the exemption ground.',
    'transfer_without_mechanism' =>
        'Data is processed outside the EEA, but no transfer mechanism has been described yet.',
    'risk_without_measure' =>
        'The risk ":risico" does not yet have a measure. If the risk has been accepted, explain this under the acceptance of residual risks.',
    'risk_deviates_without_motivation' =>
        'The risk level of ":risico" deviates from the matrix (:niveau). Explain that deviation in the motivation.',
    'measure_without_risk' =>
        'The measure ":maatregel" is not yet linked to a risk. The model requires a description of which measure addresses which risk.',
    'high_residual_risk_without_ap' =>
        'A high residual risk remains. If you cannot reduce it, the Dutch Data Protection Authority (Autoriteit Persoonsgegevens) must be consulted in advance (Article 36 GDPR).',
    'high_residual_risk_without_acceptance' =>
        'A high residual risk remains without substantiation as to why that is acceptable.',
];
