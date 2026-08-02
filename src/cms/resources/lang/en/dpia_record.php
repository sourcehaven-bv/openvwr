<?php

declare(strict_types=1);

// Labels and help texts for the DPIA register.
//
// The help texts are based on part III (explanatory notes) of the Model DPIA
// Rijksdienst v3.0. The numbering of the sections is the official numbering
// from part II of that model; it remains unchanged so that the DPIA stays
// traceable for DPOs, auditors and the Dutch Data Protection Authority.

return [
    'model_singular' => 'DPIA',
    'model_plural' => "DPIAs",
    'table_empty_heading' => "No DPIAs",
    'register_description' => 'Data protection impact assessments according to the Model DPIA Rijksdienst v3.0.',

    // Parts A to D from part II of the model.
    'part_a' => 'A. General characteristics of the processing activities',
    'part_b' => 'B. Assessment of lawfulness',
    'part_c' => "C. Risks to data subjects",
    'part_d' => 'D. Measures',
    'part_process' => 'Process and accountability',

    // Steps: the 17 sections plus the process components.
    'step_general' => 'General',
    'step_proposal' => '1. Proposal',
    'step_personal_data' => '2. Personal data',
    'step_processing' => '3. Processing activities',
    'step_techniques' => '4. Techniques and methods',
    'step_purposes' => '5. Processing purposes',
    'step_parties' => '6. Parties involved',
    'step_interests' => '7. Interests',
    'step_locations' => '8. Processing locations',
    'step_legal_framework' => '9. Legal and policy framework',
    'step_retention' => '10. Retention periods',
    'step_legal_basis' => '11. Legal basis',
    'step_special_categories' => '12. Special categories of personal data',
    'step_purpose_limitation' => '13. Purpose limitation',
    'step_necessity' => '14. Necessity and proportionality',
    'step_rights' => '15. Rights of data subjects',
    'step_risks' => "16. Risks to data subjects",
    'step_measures' => '17. Measures',
    'step_consultation' => 'Consultation and advice',
    'step_review' => 'Adoption and review',
    'step_relations' => 'Processing activities and systems',
    'step_attachments' => 'Documents and attachments',
    'step_remarks' => 'Remarks',

    // General
    'name' => 'Name of the DPIA',
    'help_name' => 'Use a name that is recognisable within the organisation, for example the name of the project, the regulation or the system.',
    'subject_type' => 'What does this DPIA concern?',
    'help_subject_type' => 'The model distinguishes between a DPIA on legislation (acts, orders in council and ministerial regulations) and a DPIA on processing activities by or on behalf of the government.',
    'subject_type_verwerking' => 'Processing activity (product, service, process or system)',
    'subject_type_regelgeving' => 'Legislation (act, order in council or ministerial regulation)',
    'prescan' => 'Associated pre-scan',
    'help_prescan' => 'The pre-scan showing that this DPIA is needed. This keeps the reason for the DPIA traceable.',

    // 1. Proposal
    'proposal_description' => 'Description of the proposal',
    'help_proposal_description' => 'Describe in broad terms what the DPIA covers. Keep it understandable for someone who does not know the project.',
    'proposal_motivation' => 'Origin and reasons',
    'help_proposal_motivation' => 'State how the proposal came about and what the reasons behind it are.',

    // 2. Personal data
    'personal_data' => 'Personal data',
    'personal_data_intro' => 'Describe all personal data that is processed and classify it. The classification determines whether an exemption ground is needed in section 12.',
    'personal_data_item_label' => 'Personal data item',
    'add_personal_data' => 'Add personal data item',
    'personal_data_description_item' => 'Which personal data item is processed?',
    'help_personal_data_description_item' => 'For example "name and address", "citizen service number" or "camera images".',
    'personal_data_type' => 'Type of personal data',
    'help_personal_data_type' => 'Special categories of personal data and criminal law data may in principle not be processed, and a national identification number only where the law provides for it. For those choices you will be asked for the exemption ground below.',
    'personal_data_type_gewoon' => 'Ordinary',
    'personal_data_type_gevoelig' => 'Sensitive',
    'personal_data_type_bijzonder' => 'Special category (Article 9 GDPR)',
    'personal_data_type_strafrechtelijk' => 'Criminal law data (Article 10 GDPR)',
    'personal_data_type_identificatienummer' => 'National identification number',
    'personal_data_subject_category' => 'Category of data subjects',
    'help_personal_data_subject_category' => 'Whose data is this? For example citizens, staff or visitors.',
    'personal_data_source' => 'Source',
    'help_personal_data_source' => 'Where does this data item come from? For example the data subject themselves, a base registry or a third party.',
    'personal_data_retention_period' => 'Retention period',
    'help_personal_data_retention_period' => 'How long is this data item retained? The substantiation belongs in section 10.',
    'personal_data_exception_ground' => 'Exemption ground',
    'help_personal_data_exception_ground' => 'On what basis may this data item nevertheless be processed? Refer to the statutory exemption (Article 9 or 10 GDPR, or the UAVG (Dutch GDPR Implementation Act)) and substantiate it.',
    'personal_data_exception_notice' => 'This type of data may in principle not be processed. Enter the exemption ground below (section 12).',
    'personal_data_sources' => 'Additional information about the personal data',
    'help_personal_data_sources' => 'Optional text field for explanations that do not belong to an individual data item.',

    // 3. Processing activities
    'processing_description' => 'Description of the processing activities',
    'help_processing_description' => 'Set out all processing activities and indicate for each activity which categories of personal data are processed in it. A flow chart may be added as an attachment.',

    // 4. Techniques and methods
    'techniques_description' => 'Manner, means and methods',
    'help_techniques_description' => 'Describe in what manner and with which (technical) means and methods the personal data is processed.',
    'automated_decision_making' => 'There is (semi-)automated decision-making',
    'profiling' => 'There is profiling',
    'cloud_processing' => 'A cloud solution is used',
    'big_data_processing' => 'There are big data processing activities',
    'techniques_explanation' => 'Explanation of the ticked techniques',
    'help_techniques_explanation' => 'Describe what the ticked techniques consist of. In the case of automated decision-making: also describe the underlying logic and the consequences for the data subject.',

    // 5. Processing purposes
    'purpose_description' => 'Purposes of the processing activities',
    'help_purpose_description' => 'Describe the purposes of all processing activities. A purpose must be specified, explicit and legitimate.',

    // 6. Parties involved
    'parties_description' => 'Parties involved and their role',
    'help_parties_description' => 'Name all parties involved per processing activity and classify them under the roles: controller, joint controller, processor, sub-processor, supplier, recipient, data subject and third party.',
    'parties_access' => 'Who gets access to which data?',
    'help_parties_access' => 'State, where known, which officers or departments within these parties get access to which categories of personal data.',

    // 7. Interests
    'interests_description' => 'Interests of the parties involved',
    'help_interests_description' => 'Describe all interests that the parties involved have in the processing activities.',
    'interests_data_subjects' => 'Views of the data subjects',
    'help_interests_data_subjects' => 'Ask data subjects or their representatives for their views on the processing where relevant, and explain those views here.',

    // 8. Processing locations
    'processing_locations' => 'In which countries do the processing activities take place?',
    'help_processing_locations' => 'Name the countries where the processing activities take place, including the locations of processors and sub-processors.',
    'outside_eea' => 'Processing activities take place outside the European Economic Area',
    'transfer_mechanism' => 'Transfer mechanism',
    'help_transfer_mechanism' => 'Describe which transfer mechanism applies, for example an adequacy decision, standard contractual clauses on data protection (SCC) or binding corporate rules.',
    'transfer_safeguards' => 'Additional measures for transfers',
    'help_transfer_safeguards' => 'State whether and which additional measures apply. Also consider whether a DTIA is needed.',

    // 9. Legal and policy framework
    'legal_policy_framework' => 'Legislation, regulations and policy',
    'help_legal_policy_framework' => 'Name all legislation, regulations and policy with possible consequences for the processing activities. The GDPR and the Directive need not be mentioned.',

    // 10. Retention periods
    'retention_periods' => 'Retention periods',
    'help_retention_periods' => 'Determine the retention periods on the basis of the processing activities and the processing purposes. Also take the Dutch Public Records Act into account.',
    'retention_motivation' => 'Motivation of the retention periods',
    'help_retention_motivation' => 'Motivate why these retention periods are no longer than strictly necessary in relation to the processing purposes.',
    'retention_responsible' => 'Who monitors the retention period?',
    'help_retention_responsible' => 'Describe who monitors the retention period and the destruction or archiving at the end of it.',

    // 11. Legal basis
    'legal_basis' => 'Legal bases',
    'help_legal_basis' => 'Determine on which legal bases the processing activities are based (Article 6 GDPR). For processing by the government these are usually a legal obligation or a task carried out in the public interest.',
    'legal_basis_conditions' => 'How are the conditions met?',
    'help_legal_basis_conditions' => 'Each legal basis sets its own conditions. Explain for each legal basis how these are met.',

    // 12. Special categories of personal data
    'special_categories_no_personal_data' => 'No personal data has been entered in section 2 yet. Enter it first; this section follows from it.',
    'special_categories_none' => 'None of the personal data from section 2 has been classified as a special category, criminal law data or a national identification number. No exemption ground is then needed.',
    'special_categories_missing_ground' => 'No exemption ground has been entered for this data yet. Enter it in section 2, with the data item itself:',
    'special_categories_with_ground' => 'An exemption ground has been recorded for this data in section 2:',
    'special_categories_additional' => 'Additional explanation of the exemption grounds',
    'help_special_categories_additional' => 'Optional. Use this field for a joint substantiation or for context that does not belong to an individual data item.',
    'special_categories' => 'Special categories of personal data or criminal law data are processed',
    'help_special_categories' => 'Processing special categories of personal data or criminal law data is in principle prohibited. Processing is only possible where a statutory exemption ground applies.',
    'special_categories_exception' => 'Which exemption ground applies?',
    'help_special_categories_exception' => 'Assess which statutory exemption to the prohibition on processing applies (Article 9 or 10 GDPR, or the UAVG (Dutch GDPR Implementation Act)) and substantiate this.',
    'national_identification_number' => 'A national identification number is processed',
    'help_national_identification_number' => 'For example the citizen service number. Its use is only permitted where the law provides for it.',
    'national_identification_number_basis' => 'Basis for the identification number',
    'help_national_identification_number_basis' => 'Assess and substantiate whether the use of the national identification number is permitted.',

    // 13. Purpose limitation
    'further_processing' => 'The data is also processed for a purpose other than the one for which it was collected',
    'help_further_processing' => 'Further processing for another purpose is only permitted if there is a statutory basis for it or if the new purpose is compatible with the original one.',
    'purpose_limitation' => 'Assessment of the purpose limitation',
    'help_purpose_limitation' => 'Assess whether the further processing is permissible under Union or Member State law, or is compatible with the purpose for which the data was originally collected.',

    // 14. Necessity and proportionality
    'necessity_proportionality' => 'Proportionality',
    'help_necessity_proportionality' => 'Is the interference with private life and the protection of personal data proportionate to the processing purposes?',
    'necessity_subsidiarity' => 'Subsidiarity',
    'help_necessity_subsidiarity' => 'Can the processing purposes reasonably not be achieved in another way that is less detrimental to the data subjects?',

    // 15. Rights of data subjects
    'data_subject_rights_procedure' => 'Procedure for the rights of data subjects',
    'help_data_subject_rights_procedure' => 'Describe how effect is given to the rights of data subjects: information, access, rectification, erasure, restriction, portability, objection and the right not to be subject to automated decision-making.',
    'rights_restricted' => 'The rights of data subjects are restricted',
    'rights_restriction_basis' => 'Basis for the restriction',
    'help_rights_restriction_basis' => 'Describe under which statutory exemption the restriction is permitted.',

    // 16. Risks
    'risks' => "Risks",
    'risks_intro' => "Describe and assess all possible risks of the processing activities to the rights and freedoms of data subjects. Think not only of privacy, but also for example of the prohibition of discrimination.",
    'risk' => 'Risk',
    'risk_title' => 'Name of the risk',
    'help_risk_title' => 'A short, recognisable name. It appears in section 17 when linking measures, for example "Incorrect identification of visitors".',
    'risk_description' => 'Description of the risk',
    'help_risk_description' => 'What negative consequences can the processing activities have for the rights and freedoms of the data subjects? Think not only of privacy, but also for example of discrimination or the denial of a service.',
    'risk_origin' => 'Origin',
    'help_risk_origin' => 'What can cause this risk to arise? Name the source or event, for example human error, a malfunction or misuse, an unauthorised person inside or outside the organisation, a processor that does not comply with the agreements, or a system that produces incorrect results.',
    'risk_likelihood' => 'Likelihood',
    'help_risk_likelihood' => 'How likely is it that this consequence will occur?',
    'risk_likelihood_motivation' => 'Motivation of the likelihood',
    'risk_impact' => 'Impact',
    'help_risk_impact' => 'How serious is this consequence for the data subjects when it occurs?',
    'risk_impact_motivation' => 'Motivation of the impact',
    'risk_level' => 'Risk level',
    'help_risk_level' => 'Filled in as soon as likelihood and impact are known. You may deviate from this, for example where a risk cannot be mitigated further; explain this in the motivation alongside.',
    'risk_level_motivation' => 'Motivation of the risk assessment',
    'risks_additional_information' => "Additional information about the risks",
    'help_risks_additional_information' => 'Optional text field for further explanation.',
    'add_risk' => 'Add risk',
    'risk_item_label' => 'Risk',
    'risk_level_laag' => 'Low',
    'risk_level_gemiddeld' => 'Medium',
    'risk_level_hoog' => 'High',
    'risk_matrix_suggestion' => 'Risk level :level follows from likelihood x impact.',
    'risk_matrix_deviation' => 'Please note: likelihood x impact indicates :level. Explain the deviation in the motivation of the risk assessment.',

    // 17. Measures
    'measures' => 'Measures',
    'measures_intro' => "Assess which technical, organisational and legal measures can reasonably be taken to prevent or reduce the risks described above. Describe for each measure which risk it addresses.",
    'measure' => 'Measure',
    'measure_description' => 'Description of the measure',
    'measure_type' => 'Type of measure',
    'help_measure_type' => 'The model asks for technical, organisational and legal measures.',
    'measure_type_technisch' => 'Technical',
    'measure_type_organisatorisch' => 'Organisational',
    'measure_type_juridisch' => 'Legal',
    'measure_risks' => 'Which risks does this measure address?',
    'help_measure_risks' => "Choose one or more risks from section 16. Enter the risks first; they will appear here automatically.",
    'measure_risks_empty' => "No risks have been entered in section 16 yet. Enter them first and save.",
    'measure_origin' => 'Origin of the measure',
    'measure_residual_level' => 'Remaining risk after this measure',
    'help_measure_residual_level' => 'Which risk remains after this measure has been carried out or implemented?',
    'measure_ap_advice' => 'Advice of the Dutch Data Protection Authority (Autoriteit Persoonsgegevens)',
    'help_measure_ap_advice' => 'Add a reference to or a description of the advice of the AP.',
    'measure_monitoring_country' => 'Country of monitoring and evaluation',
    'help_measure_monitoring_country' => 'In which country does the monitoring and evaluation of the measures take place?',
    'help_measure_origin' => 'Where does this measure come from? For example from existing policy, the BIO, a processor agreement, advice from the DPO or an earlier DPIA.',
    'measure_owner' => 'Owner of the measure',
    'help_measure_owner' => 'Who is responsible for carrying out and monitoring this measure?',
    'measures_additional_information' => 'Additional information about the measures',
    'residual_risk_acceptance' => "Substantiation of the acceptance of remaining risks",
    'help_residual_risk_acceptance' => "Give a conclusion on the residual risks. Are these acceptable? And is prior consultation with the Dutch Data Protection Authority (Autoriteit Persoonsgegevens) needed?",
    'add_measure' => 'Add measure',
    'measure_item_label' => 'Measure',

    // Process
    'data_subjects_consulted' => 'Data subjects or their representatives have been consulted',
    'help_data_subjects_consulted' => 'Article 35(9) GDPR requires the views of data subjects to be sought where appropriate. Where it concerns your own staff, involve the works council.',
    'data_subjects_consultation' => 'Outcome of the consultation',
    'help_data_subjects_consultation' => 'Record what those consulted advised and what has been done with it. If no consultation takes place, motivate that decision here.',
    'fg_advice' => 'Advice of the data protection officer',
    'help_fg_advice' => 'Seeking advice from the DPO is mandatory (Article 35(2) GDPR). Involve the DPO as early as possible and not only when the report is finished.',
    'fg_advice_followup' => 'What has been done with the advice of the DPO?',
    'fg_advice_received_at' => 'Date of DPO advice',
    'ap_consultation_required' => 'Prior consultation of the AP is needed',
    'help_ap_consultation_required' => 'Needed where the DPIA shows a high residual risk that you cannot reduce to an acceptable level (Article 36 GDPR). For a DPIA on legislation, the proposal must always be submitted to the AP.',
    'ap_consultation' => 'Advice of the AP and the follow-up to it',
    'help_ap_consultation' => 'A period of eight weeks applies to the written advice of the AP, with a maximum extension of six weeks.',
    'ap_consultation_requested_at' => 'Date of consultation of the AP',
    'ap_consultation_warning' => "One or more measures leave a high residual risk. Consult the Dutch Data Protection Authority (Autoriteit Persoonsgegevens) before the processing begins (Article 36 GDPR).",

    // Adoption and review
    'assessed_at' => 'Date of completion',
    'help_assessed_at' => 'The date on which this DPIA was carried out or last substantively reviewed.',
    'review_at' => 'Date of next review',
    'help_review_at' => 'A DPIA must be reviewed if the processing changes, and in any event every three years.',
    'review_hint' => 'Proposal based on the completion date: :date (three years).',
    'management_summary' => 'Management summary',
    'help_management_summary' => 'A short summary of the outcomes for directors and decision-makers.',

    // Links
    'avg_responsible_processing_records' => 'Processing activities (GDPR controller)',
    'help_avg_responsible_processing_records' => 'Link the processing activities from the register to which this DPIA relates. A DPIA may cover a series of similar processing activities.',
    'systems' => 'Systems and applications',
    'processors' => 'Processors',
    'responsibles' => 'Controllers',

    // Overview
    'risk_count' => "Risks",
    'highest_residual_risk' => 'Highest residual risk',
    'no_risks' => "No risks yet",
    'review_due' => 'Review needed',
];
