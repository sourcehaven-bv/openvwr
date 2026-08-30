<?php

declare(strict_types=1);

return [
    'avg_responsible_processing_record' => [
        'step_processing_name_title' => 'Information about the Name of the Processing Activity',
        'step_processing_name_info' => '
            <p class="text-sm text-gray-500">Enter here the name of the data processing activity as it is known within your organisation, such as "grant administration" or "sending newsletters".</p>',
        'step_processing_name_extra_info' => '
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Record processing activities</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">All operations on personal data, such as storing, transmitting or erasing, count as &quot;processing&quot;. Record these in the GDPR Controller register. Categorise activities such as &quot;grant administration&quot;, &quot;sending newsletters&quot; or &quot;providing a portal&quot; (including collecting, storing, sharing and erasing data).</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">General information about processing</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">The GDPR defines data processing as &quot;any operation or set of operations which is performed on personal data, whether or not by automated means.&quot; This includes collecting, recording, organising, storing, updating, retrieving, consulting, using, disclosing, disseminating, making available, combining, restricting, erasing or destroying data (Art. 4(2) GDPR).</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Processing activities that do not have to be recorded</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300">Entirely manual processing without a filing system (e.g. handwritten notes) does not have to be recorded. The same applies to personal/household activities (e.g. social media contacts outside working hours). These fall outside the scope of the GDPR. Processing activities carried out as a processor must be recorded in the &quot;GDPR Processor&quot; register.</p>',

        'step_responsible_title' => 'Information about the Controller',
        'step_responsible_info' => '
            <p class="text-sm text-gray-500">Under the GDPR the controller is: &ldquo;the natural or legal person, public authority, agency or other body which, alone or jointly with others, determines the purposes and means of the processing of personal data&rdquo; (Art. 4(7) GDPR).</p>',
        'step_responsible_extra_info' => '
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Choose the controller</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Find the controller by starting to type. If the controller is not yet in the system, press the &#39;+&#39; sign and enter the controller\'s details. Fill in the position and any contact details.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Add several controllers if necessary, e.g. for partnerships. </p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4"><span class="font-bold">Please note:</span> Only enter controllers on whose behalf you process personal data.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">General information about the controller</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">A party can be a controller because:</p>
            <ul class="list-disc list-outside mb-4 ml-5">
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">The factual circumstances (conduct) lead to this. Helpful questions in that case are: Why does this processing take place? Who initiated it? Who determines the retention periods.</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">It has been laid down as such in law, in a decision of a supervisory authority (such as the Dutch Data Protection Authority (Autoriteit Persoonsgegevens)) or in a contract</li>
            </ul>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4"><span class="font-bold">Please note:</span> Factual circumstances (conduct) carry more weight than a contract that stipulates who is the controller.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Allocation of responsibility between parties</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Where there is more than one controller, fill in the allocation between them once.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4"><span class="font-bold">Please note:</span> As a processor you have no influence on this and you are not obliged to include it in the register. Add it if it is available.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">In terms of the allocation of responsibility, the following variants are possible:</p>
            <ul class="list-disc list-outside ml-5">
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1"><span class="font-bold">Joint controllership</span>:<br>Several parties jointly determine the purposes and means. A mandatory arrangement between them is required (Article 26 GDPR).</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1"><span class="font-bold">Separate controllership</span>:<br>This is the case where parties work together but each separately determines the purposes and means.</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1"><span class="font-bold">A combination of both</span>:<br>It may be that a single processing activity involves both joint and separate responsibility.</li>
            </ul>',

        'step_processor_title' => 'Information about the Processor',
        'step_processor_info' => '
            <p class="text-sm text-gray-500 mb-4">A processor is a person or organisation engaged by a controller to process personal data on the controller\'s behalf. Under the GDPR a processor is: &ldquo;a natural or legal person, public authority, agency or other body which processes personal data on behalf of the controller&rdquo; (Article 4(8) GDPR). </p>
            <p class="text-sm text-gray-500">An example of this could be the &quot;ICT Implementation Service (DICTU) of the Ministry of Economic Affairs and Climate Policy&quot; or the cloud supplier.</p>',

        'step_receiver_title' => 'Information about Recipients',
        'step_receiver_info' => '
            <p class="text-sm text-gray-500">The GDPR understands a recipient to be: &ldquo;a natural or legal person, public authority, agency or another body, to which the personal data are disclosed, whether a third party or not. However, public authorities which may receive personal data in the framework of a particular inquiry in accordance with Union or Member State law shall not be regarded as recipients [&hellip;]&rdquo; (Art. 4(9) GDPR).</p>',
        'step_receiver_extra_info' => '
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Fill in information about the recipients</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Here you must state the persons, departments, organisations or bodies to which the personal data is disclosed. For recipients outside your organisation you can name the organisation concerned as well as the relevant unit or job group, where this is known to you. Where possible, it is further advised to state why the recipients concerned receive the personal data and, where relevant, which data is involved.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4"><span class="font-bold">Please note:</span> do not include the names of individual employees; this concerns categories only.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">General information about the recipients</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">The recipient can be an organisation (a legal person, a public authority, an agency or another body), a department or a natural person to whom you disclose personal data. The recipient may moreover be located either inside or outside the controller\'s organisation. Where a recipient is located outside the controller\'s organisation, it may hold the privacy position of processor, third party or data subject.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Examples of recipients</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">As described above, recipients can be located both inside and outside the controller\'s organisation. See below a number of examples of recipients inside and outside the controller\'s organisation:</p>
            <ul class="list-disc list-outside mb-4 ml-5">
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Departments, teams or employees charged with processing personal data for the performance of their tasks or work, such as:
                    <ul class="list-disc list-outside mb-4 ml-5">
                        <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Employees of the communications department who have access to customer data;</li>
                        <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Employees of the HR department who have access to personnel files;</li>
                        <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Security staff who have access to the footage from the security cameras;</li>
                        <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Functional administrators who for that purpose have access to the personal data within an application/system;</li>
                        <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Managers who have structural access to, for example, personal data in reports.</li>
                    </ul>
                </li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">(Sub-)processors: The (sub-)processors listed under the &lsquo;Processor&rsquo; tab in this GDPR register must be &lsquo;repeated&rsquo; here.</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Data subjects: The natural persons whose personal data is processed (see also Art. 4(1) GDPR). Their own data could also be disclosed to them on a structural basis. Think of employees receiving their payslip.</li>
                <li class="text-sm text-gray-950 dark:text-gray-300">Third parties: The persons/parties to whom personal data is disclosed and which cannot be regarded as the controller, the processor, the data subject or the persons who, under the direct authority of the controller or processor, are authorised to process personal data (see also Art. 4(10) GDPR). Unlike the processor, the &lsquo;third party&rsquo; will generally process the data for its own purposes. Third parties are for example:
                    <ul class="list-disc list-outside mb-4">
                        <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Group entities between which personal data is transferred (such as the transfer of personnel data from the subsidiary to the parent company);</li>
                        <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Other units of the same overarching organisation between which personal data is transferred;</li>
                        <li class="text-sm text-gray-950 dark:text-gray-300">Insurance companies, occupational health services, debt collection agencies, etc.</li>
                    </ul>
                </li>
            </ul>',

        'step_processing_goal_title' => 'Information about Purpose & Legal Basis',
        'step_processing_goal_info' => '
            <p class="text-sm text-gray-500">Under the GDPR, personal data may only be collected for specified, explicit and legitimate purposes (Art. 5(1)(b) GDPR). A legal basis for the processing is also required (Art. 6(1) GDPR).</p>',
        'step_processing_goal_extra_info' => '
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Fill in information about the purpose</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">State the purposes for which you process personal data.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">General information about the purpose</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Purposes may relate to part of the process, such as assessing grant applications. This means that a processing activity usually has several purposes, or that the description of the purpose consists of several parts.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">General information about the legal basis</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Personal data may only be processed if there is a legal basis. These are the possible legal bases (Art. 6 GDPR):</p>
            <ol class="list-decimal list-outside mb-4 ml-5">
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Consent of the data subject (which can be withdrawn)</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Performance of, or steps prior to entering into, a contract</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Compliance with a legal obligation</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Protection of vital interests</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Exercise of official authority or a task carried out in the public interest</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Legitimate interests of the controller or a third party</li>
            </ol>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Exception to the &lsquo;legitimate interests&rsquo; legal basis for public authorities</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Public authorities may not use legitimate interests as a basis for data processing within their public tasks. They must have another legal basis, usually a task carried out in the public interest.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300">For &quot;business operations&quot;, such as payroll administration, they can use legitimate interests.</p>',

        'step_stakeholder_data_title' => 'Information about Data Subjects and Data',
        'step_stakeholder_data_info' => '
            <p class="text-sm text-gray-500">In the GDPR the person about whom information is processed is referred to as the &quot;data subject&quot; (Art. 4(1) GDPR). Personal data is the data by which the data subject can be identified directly or indirectly.</p>',
        'step_stakeholder_data_extra_info' => '
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Fill in information about the data subjects and the data</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">State which categories of data subjects and personal data you process. Identify the categories of data subjects (such as employees, job applicants) and state which personal data you process.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">For each category of data subjects you specify which data you process and why. Add purposes that you entered earlier under &quot;Purpose &amp; Legal Basis&quot;.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">General information about personal data</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">The GDPR applies only to personal data. Personal data is all data that:</p>
            <ul class="list-disc list-outside mb-4 ml-5">
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Relates to a person;</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Identifies a person directly;</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Makes a person identifiable.</li>
            </ul>
            <ul class="list-outside mb-4">
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Re 1) The data must be about the person.</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Re 2) Data identifies a person if it is unique.</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Re 3) A person is identifiable if their identity can reasonably be established.</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Re 4) The GDPR applies only to data about natural persons, not to legal persons, animals or objects.</li>
            </ul>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">More information about personal data and the data subject? See among others the website of the Dutch Data Protection Authority (Autoriteit Persoonsgegevens) on this subject.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Information about the &quot;Collection purpose&quot;</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Describe the purpose for which the data was originally collected. This may differ from the purpose of the eventual processing.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Information about the &quot;Retention period&quot;</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Do not retain personal data for longer than necessary. Observe statutory retention periods. The Dutch Public Records Act 1995 (Archiefwet 1995) applies to government processing. Consult the applicable disposal list for retention periods.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Information about the &quot;Source&quot;</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Keep track of where the personal data comes from. This may be directly from the data subject, but also from other sources. Identify the origin clearly.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Information about the &quot;Obligation to provide data&quot;</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">State clearly whether the data subject is obliged to provide data and the possible consequences of not providing it.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">General information about special categories of personal data, criminal law data and the BSN</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Special categories of personal data are data on race, ethnicity, political opinions, religion, sexuality, trade union membership, and genetic, biometric and health data.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Processing is in principle prohibited, unless exceptions apply under the GDPR (Art. 9 GDPR) and the UAVG (Dutch GDPR Implementation Act).</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Examples in which the processing is permitted are:</p>
            <ul class="list-disc list-outside mb-4 ml-5">
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">The data subject has given explicit consent for this;</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">The processing is necessary for employment law matters or matters relating to social security;</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">The data subject has manifestly made the data public for this purpose;</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">The processing is necessary in the context of legal proceedings;</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">It is necessary in order to comply with an obligation under public international law;</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">The processing is necessary for occupational medicine;</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">The processing is necessary for public health.</li>
            </ul>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">State the exception that applies to the processing of special categories of personal data.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300"><span class="font-bold">Please note:</span> Strict conditions apply to the processing of criminal law data and citizen service numbers (BSN): see Article 10 GDPR and Article 46 UAVG (Dutch GDPR Implementation Act). State whether the conditions are met. </p>',

        'step_decision_making_title' => 'Information about Decision-making',
        'step_decision_making_info' => '
            <p class="text-sm text-gray-500">Under the GDPR the data subject has &ldquo;the right not to be subject to a decision based solely on automated processing, including profiling, which produces legal effects concerning him or her or similarly significantly affects him or her&rdquo; (Art. 22(1) GDPR). There is automated decision-making where personal data is used to arrive at a particular decision about the data subject and that decision is taken without any meaningful human input.</p>',
        'step_decision_making_extra_info' => '
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Automated decision-making</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">In this section you must state whether or not there is automated decision-making. There is automated decision-making where you or your organisation takes decisions about data subjects (for example whether or not they are entitled to a permit), by automated means (the computer takes the decision, not a human being), and which produce legal effects or similarly significantly affect them, without any human intervention.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">If there is automated decision-making, you must click &lsquo;Yes&rsquo;. You must then fill in information about:</p>
            <ul class="list-disc list-outside mb-4 ml-5">
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">The logic involved (you may think here of information about how the data is used to arrive at the decision, which mathematical and statistical procedures are followed, and which assessment or selection rules are applied to arrive at the final decision);</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">The significance and the envisaged consequences of the processing for the data subjects (for example whether or not a permit is granted).</li>
            </ul>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Prohibition</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300">It is in principle prohibited to subject data subjects to automated decision-making, including profiling, where this produces legal effects concerning the data subject or similarly significantly affects the data subject. Think of decisions taken by the computer as a result of which an employment contract is terminated, a supplier is not allowed to enter into a contract with a government service, or someone is refused a job interview. An example of profiling that significantly affects data subjects is the creation of a creditworthiness profile, on the basis of which it is then decided whether or not credit is granted to someone.</p>',

        'step_system_title' => 'Information about Applications and Systems',
        'step_system_info' => '
            <p class="text-sm text-gray-500">From the point of view of securing the personal data, and in order to optimise any recovery of that data in the event of (for example) a personal data breach, it is important to know with which information system or application the processing takes place. You should be aware that an information system or application is not the same thing as a processing activity involving personal data.</p>',
        'step_system_extra_info' => '
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Enter the names of information systems/applications</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300">Find the system / application by starting to type. Select the application from the list. If the system has not yet been entered: enter here the name of the information system(s) or application(s) by means of which the processing takes place. Please mind the correct spelling. This is in connection with the search function in this register.</p>',

        'step_security_title' => 'Information about Security',
        'step_security_info' => '
            <p class="text-sm text-gray-500">Under the GDPR the processor, (sub-)processor and the controller must take appropriate technical and organisational measures (Article 32 GDPR).</p>',
        'step_security_extra_info' => '
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Explanation of the measures</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Where relevant, briefly describe here the technical and organisational security measures for personal data, such as encryption, passwords, encrypted connections, an authorisation matrix, policy and physical access control. This description does not have to be detailed.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4"><span class="font-bold">Please note:</span> The processor agreement often contains specific arrangements on security measures. You can copy these into the register.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Pseudonymisation</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300">Where the data is pseudonymised, enter here which data is pseudonymised and which methods are applied for this.</p>',

        'step_passthrough_title' => 'Information about Transfers',
        'step_passthrough_info' => '
            <p class="text-sm text-gray-500 mb-4">Not every country protects personal data in the same way. Within the European Economic Area (EEA) the rules are comparable, which means that personal data can be transferred freely here. Outside the EEA this is different. Transfer to countries outside the EEA is only permitted if there are appropriate safeguards (see also Chapter V GDPR).</p>
            <p class="text-sm text-gray-500 mb-4">The EEA comprises all EU countries plus Norway, Liechtenstein and Iceland. These three countries have a comparable level of data protection. Personal data from the Netherlands may be transferred within the EEA without additional safeguards.</p>
            <p class="text-sm text-gray-500">Incidentally, Dutch central government cloud policy may also apply here, if the data is processed (stored) in the cloud of a third country.</p>',
        'step_passthrough_extra_info' => '
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Fill in information about the transfer</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">State whether you transfer personal data to countries outside the EEA or to international organisations. If so, state to which countries you transfer data. Check on the website of the European Commission whether the country concerned has an adequate level of protection.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">The appropriate safeguards</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">The following safeguards can be put in place for transfers to a third country or an international (public international law) organisation (think of the EU institutions, the United Nations, etc.):</p>
            <ul class="list-disc list-outside mb-4 ml-5">
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">An adequacy decision by the European Commission in which it has decided that these countries ensure an adequate level of protection (Art. 45 GDPR). <span class="font-bold">Please note:</span> adequacy decisions can be withdrawn with immediate effect by, for example, the Court of Justice of the European Union</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">A legally binding and enforceable instrument between public authorities or bodies (Art. 46(2)(a) and 46(3)(b) GDPR and recital 108 GDPR).</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Binding corporate rules within a group of undertakings or enterprises (Art. 46(3)(b) and 47 GDPR).</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">The parties have agreed the standard contractual clauses of the European Commission on data protection (Art. 46(2)(c) GDPR).</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">The parties have agreed the standard clauses of the supervisory authority (for the Netherlands: the Dutch Data Protection Authority (Autoriteit Persoonsgegevens)) (Art. 46(2)(d) GDPR).</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">An approved code of conduct applies (Art. 40 and 46(2)(e) GDPR).</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">There is a judgment of a court or a decision of an administrative authority based on a treaty (Art. 48 GDPR.</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">The supervisory authority (for the Netherlands: the Dutch Data Protection Authority (Autoriteit Persoonsgegevens)) has authorised the transfer on the basis of contractual clauses between the parties or provisions in administrative arrangements between public authorities or bodies (Art. 46(3) GDPR).</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">There is a judgment of a court or a decision of an administrative authority which is based on an international agreement (Art. 48 GDPR.</li>
            </ul>',

        'step_geb_dpia_title' => 'Information about the DPIA',
        'step_geb_dpia_info' => '
            <p class="text-sm text-gray-500">A DPIA (data protection impact assessment) is a prior assessment of a processing activity that must meet at least Article 35 GDPR and, following advice from the Data Protection Officer (DPO), must be adopted at management level. For the record of processing activities it goes too far to explain the content and the process any further.</p>',
        'step_geb_dpia_extra_info' => '
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">High risk? Then a DPIA!</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">The controller is obliged to carry out a DPIA for processing activities that entail a high risk to the data subjects. Think for example of a new processing activity or an existing processing activity with a changed risk involving special categories of personal data (such as health data).</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Here you can state whether a DPIA has been carried out in respect of the processing activities performed by the organisation. If so, you can also add this DPIA (if available) to this processing activity under the &#39;Documents &amp; Attachments&rsquo; tab.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Consult the references below for a further explanation of &quot;high risk&quot; and the criteria for whether a DPIA is mandatory for a controller.</p>
            <ul class="list-disc list-outside mb-4 ml-5">
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Government Gazette of the Kingdom of the Netherlands, Decision on the list of processing operations involving personal data for which a data protection impact assessment (DPIA) is mandatory.</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">An explanation of the 9 criteria from the WP248 Article 29 guidelines, the 9 criteria for processing operations for which a DPIA must be carried out (pages 10 to 13).</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Information from the Dutch Data Protection Authority on the conditions for and the carrying out of PIAs.</li>
                <li class="text-sm text-gray-950 dark:text-gray-300">When answering the questions, make use where necessary of internal support material within your organisation (if available), or consult the Data Protection Officer or the Privacy Officer (if available).</li>
            </ul>',

        'step_contact_person_title' => 'Information about the Contact Person',
        'step_contact_person_info' => '
            <p class="text-sm text-gray-500">For each processing activity it must be stated who the contact person for that activity is. The contact person\'s details are for internal use and are not published on the public website.</p>',

        'step_attachments_title' => 'Information about Attachments',
        'step_attachments_info' => '
            <p class="text-sm text-gray-500">If you add documents, they may also be draft versions. State clearly what kind of document it is and whether it is a draft or final. Adding, changing or deleting documents does not require a new version of the processing activity. Add remarks for clarification.</p>',

        'step_remarks_title' => 'Information about Remarks',
        'step_remarks_info' => '
            <p class="text-sm text-gray-500" mb-4>Add remarks to the processing activity. These are internal and are not made public.</p>
            <p class="text-sm text-gray-500">They may be notes about the employee involved, reminders, announcements, outstanding matters, dates of amendment, references to other processing activities or documents such as PIAs, processor agreements, arrangements adopted where there are several controllers, control measures based on a DPIA/PIA, or personal data breaches involving the information system, etc.</p>',
        'step_remarks_extra_info' => '',

        'step_publish_title' => 'Publish',
        'step_publish_info' => '
            <p class="text-sm text-gray-500 mb-4">State here from which date this processing activity may be shown on the public website.</p>
            <p class="text-sm text-gray-500"><span class="font-bold">Please note:</span> If you leave this field empty, the processing activity will never be published to the public website.</p>',
    ],

    'avg_processor_processing_record' => [
        'step_processing_name_title' => 'Information about the Name of the Processing Activity',
        'step_processing_name_info' => '
            <p class="text-sm text-gray-500">Enter here the name of the data processing activity as it is known within your organisation, such as "grant administration" or "sending newsletters".</p>',
        'step_processing_name_extra_info' => '
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Record processing activities</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">All operations on personal data, such as storing, transmitting or erasing, count as &quot;processing&quot;. Record these in the GDPR Processor register. Categorise activities such as &quot;grant administration&quot;, &quot;sending newsletters&quot; or &quot;providing a portal&quot; (including collecting, storing, sharing and erasing data).</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">General information about processing</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">The GDPR defines data processing as &quot;any operation or set of operations which is performed on personal data, whether or not by automated means.&quot; This includes collecting, recording, organising, storing, updating, retrieving, consulting, using, disclosing, disseminating, making available, combining, restricting, erasing or destroying data (Art. 4(2) GDPR).</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Processing activities that do not have to be recorded</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300">Entirely manual processing without a filing system (e.g. handwritten notes) does not have to be recorded. The same applies to personal/household activities (e.g. social media contacts outside working hours). These fall outside the scope of the GDPR. Processing activities carried out as a processor must be recorded in the &quot;GDPR Processor&quot; register.</p>',

        'step_responsible_title' => 'Information about the Controller',
        'step_responsible_info' => '
            <p class="text-sm text-gray-500">Under the GDPR the controller is: &ldquo;the natural or legal person, public authority, agency or other body which, alone or jointly with others, determines the purposes and means of the processing of personal data&rdquo; (Art. 4(7) GDPR).</p>',
        'step_responsible_extra_info' => '
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Choose the controller</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Find the controller by starting to type. If the controller is not yet in the system, press the &#39;+&#39; sign and enter the controller\'s details. Fill in the position and any contact details.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Add several controllers if necessary, e.g. for partnerships. </p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4"><span class="font-bold">Please note:</span> Only enter controllers on whose behalf you process personal data.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">General information about the controller</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">A party can be a controller because:</p>
            <ul class="list-disc list-outside mb-4 ml-5">
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">The factual circumstances (conduct) lead to this. Helpful questions in that case are: Why does this processing take place? Who initiated it? Who determines the retention periods.</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">It has been laid down as such in law, in a decision of a supervisory authority (such as the Dutch Data Protection Authority (Autoriteit Persoonsgegevens)) or in a contract</li>
            </ul>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4"><span class="font-bold">Please note:</span> Factual circumstances (conduct) carry more weight than a contract that stipulates who is the controller.</p>',

        'step_processor_title' => 'Information about the Sub-processor',
        'step_processor_info' => '
            <p class="text-sm text-gray-500">A sub-processor is someone engaged by another processor to process personal data on behalf of the controller. Under the GDPR a processor is: “a natural or legal person, public authority, agency or other body which processes personal data on behalf of the controller” (Article 4(8) GDPR).</p>',
        'step_processor_extra_info' => '
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Fill in information about the sub-processor</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">State here whether you use sub-processors. If so, fill in the required information about the sub-processors.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">General information about sub-processors</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">A sub-processor may be a legal person, public authority, agency or other body, or even a natural person. Public authorities, agencies or other bodies may for example be administrative authorities, such as ministers and municipal executives, independent administrative bodies (ZBOs) or joint arrangements.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">The sub-processor carries out processing on the instructions of the processor for the benefit of the controller, and is not under the direct authority of the processor. To determine whether someone is a sub-processor, you look at the specific activities that the engaged party performs in a particular context.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">A sub-processor performs purely executive tasks and has no say over the purpose of the data processing. The sub-processor follows the instructions of the processor, who in turn follows the instructions of the controller.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">In addition, a sub-processor does not fall under the direct authority of a processor, which means that the sub-processor is not part of the processor\'s legal entity. For example, an employee of the processor is not a sub-processor because they fall under its direct authority.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4"><span class="font-bold">Please note:</span> In principle the controller determines the purpose and the (essential characteristics of the) means of the processing. The processor merely carries it out. In practice processors often do choose the (technical) means themselves, but as long as sub-processors do not determine the purpose or, for example, do not determine which data they collect, they remain sub-processors.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Sub-processor agreement</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">As a processor you need the controller\'s authorisation (Article 28(2) GDPR) to engage sub-processors. This authorisation is usually given via the processor agreement between the controller and the processor.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">In addition, the processor must conclude a separate sub-processor agreement with each sub-processor. This imposes the same obligations on the sub-processor as those in the processor agreement between the controller and the processor.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4"><span class="font-bold">Please note:</span> You can store the sub-processor agreement under the "Documents & Attachments" tab in this register.</p>',

        'step_receiver_title' => 'Information about the Recipient',
        'step_receiver_info' => '
            <p class="text-sm text-gray-500">The GDPR understands a recipient to be: &ldquo;a natural or legal person, public authority, agency or another body, to which the personal data are disclosed, whether a third party or not. However, public authorities which may receive personal data in the framework of a particular inquiry in accordance with Union or Member State law shall not be regarded as recipients [&hellip;]&rdquo; (Art. 4(9) GDPR).</p>',

        'step_receiver_extra_info' => '
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Fill in information about the recipients</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Here you must state the persons, departments, organisations or bodies to which the personal data is disclosed. For recipients outside your organisation you can name the organisation concerned as well as the relevant unit or job group, where this is known to you. Where possible, it is further advised to state why the recipients concerned receive the personal data and, where relevant, which data is involved.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4"><span class="font-bold">Please note:</span> do not include the names of individual employees; this concerns categories only.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">General information about the recipients</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">The recipient can be an organisation (a legal person, a public authority, an agency or another body), a department or a natural person to whom you disclose personal data. The recipient may moreover be located either inside or outside the controller\'s organisation. Where a recipient is located outside the controller\'s organisation, it may hold the privacy position of processor, third party or data subject.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Examples of recipients</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">As described above, recipients can be located both inside and outside the controller\'s organisation. See below a number of examples of recipients inside and outside the controller\'s organisation:</p>
            <ul class="list-disc list-outside mb-4 ml-5">
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Departments, teams or employees charged with processing personal data for the performance of their tasks or work, such as:
                    <ul class="list-disc list-outside mb-4 ml-5">
                        <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Employees of the communications department who have access to customer data;</li>
                        <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Employees of the HR department who have access to personnel files;</li>
                        <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Security staff who have access to the footage from the security cameras;</li>
                        <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Functional administrators who for that purpose have access to the personal data within an application/system;</li>
                        <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Managers who have structural access to, for example, personal data in reports.</li>
                    </ul>
                </li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">(Sub-)processors: The (sub-)processors listed under the &lsquo;Sub-processor&rsquo; tab in this GDPR register must be &lsquo;repeated&rsquo; here.</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Data subjects: The natural persons whose personal data is processed (see also Art. 4(1) GDPR). Their own data could also be disclosed to them on a structural basis. Think of employees receiving their payslip.</li>
                <li class="text-sm text-gray-950 dark:text-gray-300">Third parties: The persons/parties to whom personal data is disclosed and which cannot be regarded as the controller, the processor, the data subject or the persons who, under the direct authority of the controller or processor, are authorised to process personal data (see also Art. 4(10) GDPR). Unlike the processor, the &lsquo;third party&rsquo; will generally process the data for its own purposes. Third parties are for example:
                    <ul class="list-disc list-outside mb-4">
                        <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Group entities between which personal data is transferred (such as the transfer of personnel data from the subsidiary to the parent company);</li>
                        <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Other units of the same overarching organisation between which personal data is transferred;</li>
                        <li class="text-sm text-gray-950 dark:text-gray-300">Insurance companies, occupational health services, debt collection agencies, etc.</li>
                    </ul>
                </li>
            </ul>',


        'step_processing_goal_title' => 'Information about Purpose & Legal Basis',
        'step_processing_goal_info' => '
            <p class="text-sm text-gray-500">Under the GDPR, personal data may only be collected for specified, explicit and legitimate purposes (Art. 5(1)(b) GDPR). A legal basis for the processing is also required (Art. 6(1) GDPR).</p>',
        'step_processing_goal_extra_info' => '
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Fill in information about the purpose</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">State the purposes for which you process personal data.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">General information about the purpose</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Purposes may relate to part of the process, such as assessing grant applications. This means that a processing activity usually has several purposes, or that the description of the purpose consists of several parts.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">General information about the legal basis</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Personal data may only be processed if there is a legal basis. These are the possible legal bases (Art. 6 GDPR):</p>
            <ol class="list-decimal list-outside mb-4 ml-5">
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Consent of the data subject (which can be withdrawn)</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Performance of, or steps prior to entering into, a contract</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Compliance with a legal obligation</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Protection of vital interests</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Exercise of official authority or a task carried out in the public interest</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Legitimate interests of the controller or a third party</li>
            </ol>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Exception to the &lsquo;legitimate interests&rsquo; legal basis for public authorities</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Public authorities may not use legitimate interests as a basis for data processing within their public tasks. They must have another legal basis, usually a task carried out in the public interest.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300">For &quot;business operations&quot;, such as payroll administration, they can use legitimate interests.</p>',

        'step_stakeholder_data_title' => 'Information about Data Subjects and Data',
        'step_stakeholder_data_info' => '
            <p class="text-sm text-gray-500">In the GDPR the person about whom information is processed is referred to as the &quot;data subject&quot; (Art. 4(1) GDPR). Personal data is the data by which the data subject can be identified directly or indirectly.</p>',
        'step_stakeholder_data_extra_info' => '
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Fill in information about the data subjects and the data</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">State which categories of data subjects and personal data you process. Identify the categories of data subjects (such as employees, job applicants) and state which personal data you process.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">For each category of data subjects you specify which data you process and why. Add purposes that you entered earlier under &quot;Purpose &amp; Legal Basis&quot;.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">General information about personal data</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">The GDPR applies only to personal data. Personal data is all data that:</p>
            <ul class="list-disc list-outside mb-4 ml-5">
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Relates to a person;</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Identifies a person directly;</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Makes a person identifiable.</li>
            </ul>
            <ul class="list-outside mb-4">
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Re 1) The data must be about the person.</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Re 2) Data identifies a person if it is unique.</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Re 3) A person is identifiable if their identity can reasonably be established.</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Re 4) The GDPR applies only to data about natural persons, not to legal persons, animals or objects.</li>
            </ul>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">More information about personal data and the data subject? See among others the website of the Dutch Data Protection Authority (Autoriteit Persoonsgegevens) on this subject.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Information about the &quot;Collection purpose&quot;</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Describe the purpose for which the data was originally collected. This may differ from the purpose of the eventual processing.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Information about the &quot;Retention period&quot;</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Do not retain personal data for longer than necessary. Observe statutory retention periods. The Dutch Public Records Act 1995 (Archiefwet 1995) applies to government processing. Consult the applicable disposal list for retention periods.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Information about the &quot;Source&quot;</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Keep track of where the personal data comes from. This may be directly from the data subject, but also from other sources. Identify the origin clearly.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Information about the &quot;Obligation to provide data&quot;</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">State clearly whether the data subject is obliged to provide data and the possible consequences of not providing it.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">General information about special categories of personal data, criminal law data and the BSN</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Special categories of personal data are data on race, ethnicity, political opinions, religion, sexuality, trade union membership, and genetic, biometric and health data.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Processing is in principle prohibited, unless exceptions apply under the GDPR (Art. 9 GDPR) and the UAVG (Dutch GDPR Implementation Act).</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Examples in which the processing is permitted are:</p>
            <ul class="list-disc list-outside mb-4 ml-5">
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">The data subject has given explicit consent for this;</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">The processing is necessary for employment law matters or matters relating to social security;</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">The data subject has manifestly made the data public for this purpose;</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">The processing is necessary in the context of legal proceedings;</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">It is necessary in order to comply with an obligation under public international law;</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">The processing is necessary for occupational medicine;</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">The processing is necessary for public health.</li>
            </ul>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">State the exception that applies to the processing of special categories of personal data.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300"><span class="font-bold">Please note:</span> Strict conditions apply to the processing of criminal law data and citizen service numbers (BSN): see Article 10 GDPR and Article 46 UAVG (Dutch GDPR Implementation Act). State whether the conditions are met. </p>',

        'step_decision_making_title' => 'Information about Decision-making',
        'step_decision_making_info' => '
            <p class="text-sm text-gray-500">Under the GDPR the data subject has &ldquo;the right not to be subject to a decision based solely on automated processing, including profiling, which produces legal effects concerning him or her or similarly significantly affects him or her&rdquo; (Art. 22(1) GDPR). There is automated decision-making where personal data is used to arrive at a particular decision about the data subject and that decision is taken without any meaningful human input.</p>',
        'step_decision_making_extra_info' => '
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Automated decision-making</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">In this section you must state whether or not there is automated decision-making. There is automated decision-making where you or your organisation takes decisions about data subjects (for example whether or not they are entitled to a permit), by automated means (the computer takes the decision, not a human being), and which produce legal effects or similarly significantly affect them, without any human intervention.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">If there is automated decision-making, you must click &lsquo;Yes&rsquo;. You must then fill in information about:</p>
            <ul class="list-disc list-outside mb-4 ml-5">
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">The logic involved (you may think here of information about how the data is used to arrive at the decision, which mathematical and statistical procedures are followed, and which assessment or selection rules are applied to arrive at the final decision);</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">The significance and the envisaged consequences of the processing for the data subjects (for example whether or not a permit is granted).</li>
            </ul>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Prohibition</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300">It is in principle prohibited to subject data subjects to automated decision-making, including profiling, where this produces legal effects concerning the data subject or similarly significantly affects the data subject. Think of decisions taken by the computer as a result of which an employment contract is terminated, a supplier is not allowed to enter into a contract with a government service, or someone is refused a job interview. An example of profiling that significantly affects data subjects is the creation of a creditworthiness profile, on the basis of which it is then decided whether or not credit is granted to someone.</p>',

        'step_system_title' => 'Information about Applications and Systems',
        'step_system_info' => '
            <p class="text-sm text-gray-500">From the point of view of securing the personal data, and in order to optimise any recovery of that data in the event of (for example) a personal data breach, it is important to know with which information system or application the processing takes place. You should be aware that an information system or application is not the same thing as a processing activity involving personal data.</p>',
        'step_system_extra_info' => '
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Enter the names of information systems/applications</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300">Find the system / application by starting to type. Select the application from the list. If the system has not yet been entered: enter here the name of the information system(s) or application(s) by means of which the processing takes place. Please mind the correct spelling. This is in connection with the search function in this register.</p>',

        'step_security_title' => 'Information about Security',
        'step_security_info' => '
            <p class="text-sm text-gray-500">Under the GDPR the processor, (sub-)processor and the controller must take appropriate technical and organisational measures (Article 32 GDPR).</p>',
        'step_security_extra_info' => '
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Explanation of the measures</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Where relevant, briefly describe here the technical and organisational security measures for personal data, such as encryption, passwords, encrypted connections, an authorisation matrix, policy and physical access control. This description does not have to be detailed.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4"><span class="font-bold">Please note:</span> The processor agreement often contains specific arrangements on security measures. You can copy these into the register.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Pseudonymisation</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300">Where the data is pseudonymised, enter here which data is pseudonymised and which methods are applied for this.</p>',

        'step_passthrough_title' => 'Information about Transfers',
        'step_passthrough_info' => '
            <p class="text-sm text-gray-500 mb-4">Not every country protects personal data in the same way. Within the European Economic Area (EEA) the rules are comparable, which means that personal data can be transferred freely here. Outside the EEA this is different. Transfer to countries outside the EEA is only permitted if there are appropriate safeguards (see also Chapter V GDPR).</p>
            <p class="text-sm text-gray-500 mb-4">The EEA comprises all EU countries plus Norway, Liechtenstein and Iceland. These three countries have a comparable level of data protection. Personal data from the Netherlands may be transferred within the EEA without additional safeguards.</p>
            <p class="text-sm text-gray-500">Incidentally, Dutch central government cloud policy may also apply here, if the data is processed (stored) in the cloud of a third country.</p>',
        'step_passthrough_extra_info' => '
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Fill in information about the transfer</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">State whether you transfer personal data to countries outside the EEA or to international organisations. If so, state to which countries you transfer data. Check on the website of the European Commission whether the country concerned has an adequate level of protection.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">The appropriate safeguards</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">The following safeguards can be put in place for transfers to a third country or an international (public international law) organisation (think of the EU institutions, the United Nations, etc.):</p>
            <ul class="list-disc list-outside mb-4 ml-5">
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">An adequacy decision by the European Commission in which it has decided that these countries ensure an adequate level of protection (Art. 45 GDPR). <span class="font-bold">Please note:</span> adequacy decisions can be withdrawn with immediate effect by, for example, the Court of Justice of the European Union</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">A legally binding and enforceable instrument between public authorities or bodies (Art. 46(2)(a) and 46(3)(b) GDPR and recital 108 GDPR).</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Binding corporate rules within a group of undertakings or enterprises (Art. 46(3)(b) and 47 GDPR).</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">The parties have agreed the standard contractual clauses of the European Commission on data protection (Art. 46(2)(c) GDPR).</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">The parties have agreed the standard clauses of the supervisory authority (for the Netherlands: the Dutch Data Protection Authority (Autoriteit Persoonsgegevens)) (Art. 46(2)(d) GDPR).</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">An approved code of conduct applies (Art. 40 and 46(2)(e) GDPR).</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">There is a judgment of a court or a decision of an administrative authority based on a treaty (Art. 48 GDPR.</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">The supervisory authority (for the Netherlands: the Dutch Data Protection Authority (Autoriteit Persoonsgegevens)) has authorised the transfer on the basis of contractual clauses between the parties or provisions in administrative arrangements between public authorities or bodies (Art. 46(3) GDPR).</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">There is a judgment of a court or a decision of an administrative authority which is based on an international agreement (Art. 48 GDPR.</li>
            </ul>',

        'step_geb_dpia_title' => 'Information about the DPIA',
        'step_geb_dpia_info' => '
            <p class="text-sm text-gray-500">A DPIA (data protection impact assessment) is a prior assessment of a processing activity that must meet at least Article 35 GDPR and, following advice from the Data Protection Officer (DPO), must be adopted at management level. For the record of processing activities it goes too far to explain the content and the process any further.</p>',
        'step_geb_dpia_extra_info' => '
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">High risk? Then a DPIA!</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">The controller is obliged to carry out a DPIA for processing activities that entail a high risk to the data subjects. Think for example of a new processing activity or an existing processing activity with a changed risk involving special categories of personal data (such as health data).</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Here you can state whether a DPIA has been carried out in respect of the processing activities performed by the organisation. If so, you can also add this DPIA (if available) to this processing activity under the &#39;Documents &amp; Attachments&rsquo; tab.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Consult the references below for a further explanation of &quot;high risk&quot; and the criteria for whether a DPIA is mandatory for a controller.</p>
            <ul class="list-disc list-outside mb-4 ml-5">
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Government Gazette of the Kingdom of the Netherlands, Decision on the list of processing operations involving personal data for which a data protection impact assessment (DPIA) is mandatory.</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">An explanation of the 9 criteria from the WP248 Article 29 guidelines, the 9 criteria for processing operations for which a DPIA must be carried out (pages 10 to 13).</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Information from the Dutch Data Protection Authority on the conditions for and the carrying out of PIAs.</li>
                <li class="text-sm text-gray-950 dark:text-gray-300">When answering the questions, make use where necessary of internal support material within your organisation (if available), or consult the Data Protection Officer or the Privacy Officer (if available).</li>
            </ul>',

        'step_contact_person_title' => 'Information about the Contact Person',
        'step_contact_person_info' => '
            <p class="text-sm text-gray-500">For each processing activity it must be stated who the contact person for that activity is. The contact person\'s details are for internal use and are not published on the public website.</p>',

        'step_attachments_title' => 'Information about Attachments',
        'step_attachments_info' => '
            <p class="text-sm text-gray-500">If you add documents, they may also be draft versions. State clearly what kind of document it is and whether it is a draft or final. Adding, changing or deleting documents does not require a new version of the processing activity. Add remarks for clarification.</p>',

        'step_remarks_title' => 'Information about Remarks',
        'step_remarks_info' => '
            <p class="text-sm text-gray-500" mb-4>Add remarks to the processing activity. These are internal and are not made public.</p>
            <p class="text-sm text-gray-500">They may be notes about the employee involved, reminders, announcements, outstanding matters, dates of amendment, references to other processing activities or documents such as PIAs, processor agreements, arrangements adopted where there are several controllers, control measures based on a DPIA/PIA, or personal data breaches involving the information system, etc.</p>',
        'step_remarks_extra_info' => '',
    ],

    'wpg_processing_record' => [
        'step_processing_name_title' => 'Information about the Name of the Processing Activity',
        'step_processing_name_info' => '
            <p class="text-sm text-gray-500">Enter here the name of the data processing activity as it is known within your organisation, such as "grant administration" or "sending newsletters".</p>',
        'step_processing_name_extra_info' => '
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Record processing activities</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">All operations on personal data, such as storing, transmitting or erasing, count as &quot;processing&quot;. Record these in the Wpg (Dutch Police Data Act) Controller register. Categorise activities such as &quot;grant administration&quot;, &quot;sending newsletters&quot; or &quot;providing a portal&quot; (including collecting, storing, sharing and erasing data).</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">General information about processing</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">The Wpg defines data processing as &quot;any operation or set of operations relating to police data or a set of police data, whether or not carried out by automated means, such as collecting, recording, organising, structuring, storing, updating or altering, retrieving, consulting, disclosing by means of transmission, disseminating or otherwise making available, aligning, combining, restricting or destroying police data.&quot; (Art. 1(c) Wpg).</p>',

        'step_responsible_title' => 'Information about the Controller',
        'step_responsible_info' => '
            <p class="text-sm text-gray-500">Under or pursuant to the Wpg (Dutch Police Data Act) the controller is designated who is formally responsible for the proper implementation of the Wpg.</p>',
        'step_responsible_extra_info' => '
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Choose the controller</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Find the controller by starting to type. If the controller is not yet in the system, press the &#39;+&#39; sign and enter the controller\'s details. Fill in the position and any contact details.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Add several controllers if necessary, e.g. for partnerships.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">General information about the controller</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">The controller for the implementation of the Wpg by the Royal Netherlands Marechaussee is the Minister of Defence. For the special investigation services this is the minister of the department under which the special investigation service falls. The controller for the special investigating officer (Boa) is the employer of the Boa concerned.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300"><span class="font-bold">Please note</span>: The controller is not the processor of the police data. A processor is ’a natural or legal person, public authority, agency or other body which processes police data on behalf of the controller’ (Art. 6(c) Wpg). This is often a person or organisation to whom the controller has outsourced the data processing. A processor is not independently responsible for the processing of the police data, but does have a number of derived obligations. For example, the processor must follow the controller\'s instructions and must guarantee that appropriate technical and organisational security measures are implemented. In a separate question you can enter the details of any processor.</p>',

        'step_processor_title' => 'Information about the Processor',
        'step_processor_info' => '
            <p class="text-sm text-gray-500 mb-4">The Wpg (Dutch Police Data Act) defines the processor as: “a natural or legal person, public authority, agency or other body which processes police data on behalf of the controller” (Art. 6(c) Wpg).</p>
            <p class="text-sm text-gray-500">This is often a person or organisation to whom the controller has outsourced the data processing. An example is a provider of data storage in an application managed by that provider; where the service relates purely to the storage of data, the provider is a processor.</p>',
        'step_processor_extra_info' => '
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">General information about the processor</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">The processor may be a legal person, a public authority, an agency or another body, or a natural person. Public authorities, agencies or other bodies are for example administrative authorities (such as ministers and municipal executives), independent administrative bodies (ZBOs) or joint arrangements.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">The processor is a party that carries out processing on behalf of the controller and that does not fall under the direct authority of the controller. To assess whether a party can be regarded as a processor, you must look at the specific activities that an engaged party performs in a particular context.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">A party processes the police data on behalf of another party where it has a purely executive task and no say over the purpose for which the processing of personal data takes place. In processing the data, the processor follows the controller\'s instructions.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">In addition, a processor does not fall under the direct authority of a controller. This means that the processor does not fall within the controller\'s legal entity. An employee of the controller is therefore not a processor (because they fall under its direct authority). A self-employed contractor, by contrast, is a processor (because it is a separate legal entity).</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4"><span class="font-bold">Please note:</span> In principle the controller determines the purpose and the (essential characteristics of the) means of the processing, and the processor merely carries out the processing. In practice, however, processors often do choose themselves which (technical) means they use for the processing (such as which software or hardware). As long as they do not determine the purpose of the processing, and for example do not determine which data they collect, from whom and how long they retain it, they continue to be regarded as a processor. The controller determines how far the processor\'s mandate extends in this respect.</p>

            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Processing agreement</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">A contract or other binding legal act (see also Art. 6c(2) Wpg and Art. 6:1b of the Police Data Decree) must be concluded between a controller and a processor, in order to ensure that the processor will handle the controller\'s police data with due care. This is generally referred to as the ‘processor agreement’. In the processor agreement you must arrange a number of matters, such as the purpose of and the say over the processing, procedures and the security of the personal data.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4"><span class="font-bold">Please note:</span> You can store the processor agreement under the ‘Documents & Attachments’ tab in this register.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">In addition, there are a number of obligations in the Wpg that are (also) addressed independently to the processor. These concern the security of data, keeping a record of processing activities and applying the principles relating to Privacy by Design. Processors must therefore also set up their own record of processing activities, containing similar details to those included in the controller\'s record. And last but not least, they bear their own security obligation and are themselves liable for damage arising from the agreed work.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300">A controller may only engage processors that can guarantee sufficient safeguards with regard to compliance with the Wpg. You must therefore satisfy yourself that the safeguards are provided that are necessary in view of the privacy risks entailed by the processing you wish to outsource. Information security risk analyses and data protection impact assessments (DPIAs) must play an important role here.</p>',

        'step_receiver_title' => 'Information about the Recipient',
        'step_receiver_info' => '
            <p class="text-sm text-gray-500">The Wpg (Dutch Police Data Act) and subordinate legislation provide that police data may in certain cases and for specific purposes be transferred to other persons or bodies. In this register these are brought together under the heading ‘recipients’.</p>',
        'step_receiver_extra_info' => '
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">The Wpg provides that police data may be disclosed to persons or bodies in the Netherlands that do not fall within the scope of the Wpg, but that need certain police data for the performance of their task. The starting point is that police data may not be disclosed to third parties outside the Wpg domain, unless this is provided for in the Wpg or subordinate legislation. (Art. 16, 17, 18, 19, 20, 22, 23 and 24 Wpg).</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Where police data is disclosed to persons in the Netherlands who are likewise charged with the performance of a statutory criminal investigation task, this falls under the definition of making police data available. Police data may only be made available to persons in the Netherlands who are authorised in accordance with the provisions of the Wpg (Art. 15 Wpg). Police data may also be made available to investigation bodies in other Member States of the European Union that have implemented the ‘Law Enforcement Directive’ in their national legislation, and to European countries that are not EU Member States but are party to the Schengen Agreement (e.g. Norway, Iceland and Switzerland). Police data may also be made available to bodies and agencies charged with criminal law tasks under European law; for example Europol and Eurojust (Art. 15(a) Wpg).</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Under strict conditions, police data may be disclosed to investigation bodies in countries that do not fall within the scope of the ‘Law Enforcement Directive’ or to international organisations charged with criminal law tasks other than in a European context; for example Interpol (Art. 17(a) Wpg). This form of disclosure is defined as the transfer of police data.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Transfer of police data</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300">Disclosing, making available and transferring police data covers every form of making known or transferring police data, regardless of how this is done. It may be done in writing or by electronic means, but also by handing over a magnetic tape containing data. The transfer of police data may also take place by allowing the data to be consulted.</p>
        ',

        'step_wpg_goal_title' => 'Information about Purpose & Legal Basis',
        'step_wpg_goal_info' => '
            <p class="text-sm text-gray-500 mb-4">The Wpg (Dutch Police Data Act) lays down as a principle that police data may only be processed for well-defined, explicitly described purposes.</p>
            <p class="text-sm text-gray-500">The starting point is that the processing of police data is in principle only permitted where it takes place within the framework of the nine purposes described in the Wpg.</p>',

        'step_special_police_data_title' => 'Information about Special Categories of Police Data',
        'step_special_police_data_info' => '
            <p class="text-sm text-gray-500">Special categories of police data may only be processed subject to conditions. The processing must be unavoidable for its purpose, must be in addition to the processing of other police data concerning the person, and the data must be additionally secured in an appropriate manner.</p>',

        'step_decision_making_title' => 'Information about Decision-making',
        'step_decision_making_info' => '
            <p class="text-sm text-gray-500">Article 7a of the Wpg (Dutch Police Data Act) provides that a decision based solely on automated processing, including profiling, which has adverse legal effects for the data subject or significantly affects them, is prohibited, unless prior human intervention by or on behalf of the controller and specific information to the data subject are provided for. The Wpg understands profiling to mean: any form of automated processing of personal data whereby, on the basis of that data, certain personal aspects of a natural person are evaluated, in particular with a view to analysing or predicting aspects concerning that person\'s performance at work, economic situation, health, personal preferences, interests, reliability, behaviour, location or movements.</p>',
        'step_decision_making_extra_info' => '
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Automated decision-making</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">In this section you must state whether or not there is automated decision-making. There is automated decision-making where you or your organisation takes decisions about data subjects (for example whether or not they are entitled to a permit), by automated means (the computer takes the decision, not a human being), and which produce legal effects or similarly significantly affect them.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">If there is automated decision-making, you must state this. You must then fill in useful information about the logic involved, as well as the significance and the envisaged consequences of that processing for the data subject. Data subjects who are affected must be informed about this. Under certain conditions, however, the controller may postpone, restrict or omit this duty to inform in so far as this is a necessary and proportionate measure. For example, where the information would have adverse consequences for the criminal investigation. If use is made of this, it must be taken into account when including the processing activity in the register.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Prohibition</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">It is in principle prohibited to subject data subjects to automated decision-making, including profiling, where this has adverse legal effects for the data subject or otherwise significantly affects the data subject.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">When is automated decision-making permitted?</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">The prohibition on automated decision-making (including profiling) does however have exceptions, namely where prior human intervention by or on behalf of the controller is provided for. Such a decision may not be based on special categories of police data (Art. 5 Wpg) unless the Dutch Data Protection Authority has been consulted about the intended processing. Profiling on the basis of special categories of police data that leads to discrimination against individuals is in any event not permitted.</p>',

        'step_system_application_title' => 'Information about Systems / Applications',
        'step_system_application_info' => '
            <p class="text-sm text-gray-500">From the point of view of securing the personal data, and in order to optimise any recovery of that data in the event of (for example) a personal data breach, it is important to know with which information system or application the processing takes place. You should be aware that an information system or application is not the same thing as a processing activity involving personal data.</p>',
        'step_system_application_extra_info' => '
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Processing activities involving police data are always directed at particular purposes arising from the purpose-based processing set out in Articles 8, 9, 10, 12 or 13 Wpg. An example of a processing activity involving police data is a large-scale criminal investigation (Art. 9 Wpg). To achieve the purpose of a processing activity involving police data, one or more systems with certain hardware, software and data (files) may be used for the operations. Conversely, several “processing activities involving police data” may be carried out via a single system. In order to secure the police data properly, it is important to have an overview of which systems are used.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Enter the names of systems/applications</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Enter here the name of the information system(s) or application(s) by means of which the processing takes place. You can enter several systems. Please mind the correct spelling. This is in connection with the search function in this register.</p>',

        'step_security_title' => 'Information about Security',
        'step_security_info' => '
            <p class="text-sm text-gray-500">Pursuant to Art. 4a Wpg (Dutch Police Data Act), the controller and the processor must take appropriate technical and organisational measures in order to: a. ensure, and be able to demonstrate, that the processing of police data is carried out in accordance with the provisions of the Wpg; b. implement and apply the data protection policy and the data protection principles in an effective manner; c. build the necessary safeguards, such as pseudonymisation, into the processing when determining the means of processing and during the processing itself, in order to comply with the Wpg and to protect the rights of the persons whose police data is processed. The technical and organisational measures must be designed in such a way that a level of security is ensured that is appropriate to the risk, in particular with regard to the processing of special categories of police data, and in such a way that the police data is protected against unauthorised or unlawful processing and against intentional loss, destruction or damage.</p>',
        'step_security_extra_info' => '
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Fill in information about the security measures</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">The information you enter under ‘Security’ is intended to give a general description of the technical and organisational security measures that have been taken to secure the police data appropriately, both technically (e.g. encryption, passwords, encrypted connections, etc.) and organisationally (e.g. an authorisation matrix, policy and physical access control). Only a general description of the security measures needs to be included in this register. The information in this register is not intended, for example, to correspond one-to-one with the content of an ISMS, or to replace such a system.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Pseudonymisation</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300">The Wpg does not define pseudonymisation, but does mention it as a measure to safeguard the rights of data subjects. Article 3(5) of the Law Enforcement Directive understands pseudonymisation to mean the processing of personal data in such a manner that the personal data can no longer be attributed to a specific data subject without the use of additional information, provided that such additional information is kept separately and is subject to technical and organisational measures to ensure that the personal data are not attributed to an identified or identifiable natural person.</p>',

        'step_geb_dpia_title' => 'Information about the DPIA',
        'step_geb_dpia_info' => '
            <p class="text-sm text-gray-500">In practice various terms for this prior assessment are used interchangeably, such as PIA (privacy impact assessment), GEB or GBEB (gegevensbeschermingseffectbeoordeling) and DPIA (data protection impact assessment). These all amount to the same thing, namely a written assessment to comply with the obligation under the Wpg (Dutch Police Data Act) to map out the risks and identify risk-mitigating measures for processing activities that are likely to entail a high risk to the privacy protection of individuals. The Wpg assigns to the controller the task of assessing whether the risk-mitigating measures have in fact been implemented.</p>',
        'step_geb_dpia_extra_info' => '
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">High risk? Then a DPIA!</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">The controller is obliged to carry out a DPIA for processing activities that entail a high risk to the data subjects. As a processor you must support the controller in carrying out a DPIA, if the controller requests this. This support may take the form of, for example, providing requested information about processing activities performed by the processor. Specific arrangements on, for example, the allocation of costs for the processor\'s support with a DPIA may be laid down in a processor agreement.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Here you can state whether the controller has carried out a DPIA in respect of the processing activities performed by you. If so, you can also add this DPIA (if available) to your GDPR register under the ‘Documents & Attachments’ tab.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Consult the references below for a further explanation of "high risk" and the criteria for whether a DPIA is mandatory for a controller.</p>
            <ul class="list-disc list-outside mb-4 ml-5">
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Government Gazette of the Kingdom of the Netherlands, Decision on the list of processing operations involving personal data for which a data protection impact assessment (DPIA) is mandatory.</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">An explanation of the 9 criteria from the WP248 Article 29 guidelines, the 9 criteria for processing operations for which a DPIA must be carried out (pages 10 to 13).</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Information from the Dutch Data Protection Authority on the conditions for and the carrying out of PIAs.</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">When answering the questions, make use where necessary of internal support material within your organisation (if available), or consult the Data Protection Officer or the Privacy Officer (if available).</li>
            </ul>',

        'step_contact_person_title' => 'Information about the Contact Person',
        'step_contact_person_info' => '
            <p class="text-sm text-gray-500">For each processing activity it must be stated who the contact person for that activity is. The contact person is generally the person authorised to complete and use this register. The employees who review the processing activities or the DPO can contact this person if there are questions about the processing activity.</p>',

        'step_attachments_title' => 'Information about Documents & Attachments',
        'step_attachments_info' => '
            <p class="text-sm text-gray-500">If you add documents, they may also be draft versions. State clearly what kind of document it is and whether it is a draft or final. Adding, changing or deleting documents does not require a new version of the processing activity. Add remarks for clarification.</p>',

        'step_remarks_title' => 'Information about Remarks',
        'step_remarks_info' => '
            <p class="text-sm text-gray-500 mb-4">Add remarks to the processing activity. These are internal and are not made public.</p>
            <p class="text-sm text-gray-500">They may be notes about the employee involved, reminders, announcements, outstanding matters, dates of amendment, references to other processing activities or documents such as PIA\'s, processor agreements, arrangements adopted where there are several controllers, control measures based on a DPIA/PIA, or personal data breaches involving the information system, etc.</p>',
        'step_remarks_extra_info' => '',

        'step_categories_involved_title' => 'Information about Categories of Data Subjects',
        'step_categories_involved_info' => '
            <p class="text-sm text-gray-500">As far as possible, a clear distinction must be made between police data concerning different categories of data subjects. When processing this data it must be taken into account that a category may change in respect of a particular natural person.</p>',
    ],

    'algorithm_record' => [
        'step_processing_name_title' => 'Information about the Name of the Algorithm',
        'step_processing_name_info' => '
            <p class="text-sm text-gray-500">Enter here the name of the algorithm as it is known within your organisation.</p>',
        'step_processing_name_extra_info' => '...',

        'step_responsible_use_title' => 'Information about Responsible Use',
        'step_responsible_use_info' => '
            <p class="text-sm text-gray-500">Under "Purpose and impact", describe the purpose of the algorithm, the problem it solves and the expected benefits. Under "Considerations", note the ethical considerations and trade-offs made during development. Under "Human intervention", state how and when human intervention takes place, and explain the risk control measures under "Risk management". Under "Title of the legal basis", enter the name of the legal basis, describe it under "Legal basis" and add a "Link to the legal basis". Under "Link to the record of processing activities", refer to the public part of the record of processing activities. Under "Impact assessments", describe which assessments have been carried out (such as a DPIA or IAMA), add "Links to impact assessments" and explain under "Explanation of the impact assessments" why certain assessments were or were not carried out.</p>',

        'step_mechanics_title' => 'Information about Operation',
        'step_mechanics_info' => '
            <p class="text-sm text-gray-500">Under "Title of the data source", enter the name of the data source used. Under "Data", describe which data is used by the algorithm. Under "Links to data sources", add the relevant sources. Under "Technical operation", explain how the algorithm functions and which techniques it uses. Under "Supplier", state the external supplier of the algorithm, or "Developed in-house" where the organisation built it itself. Under "Link to source code", add a link to the source code of the algorithm, if available.</p>',

        'step_meta_title' => 'Information about Metadata',
        'step_meta_info' => '
            <p class="text-sm text-gray-500">Stating under "Date of development" when an algorithm was developed helps AI compliance officers keep an overview of recent or still to be developed algorithms. Under "Owner of the algorithm", state who bears ultimate responsibility (e.g. the process owner). Under "Product owner of the algorithm", state who is operationally responsible for management and further development. Under "External registration number", state the identification number that an external register has assigned to this registration, for example the Dutch national Algorithm Register. Under "Source ID", note the unique identifier for this registration as used within your own organisation. Under "Search terms", add relevant keywords that describe the algorithm, separated by commas. These search terms are not visible on the website, but they do improve findability.</p>',

        'step_impact_title' => 'Information about High-impact algorithms',
        'step_impact_info' => '
            <p class="text-sm text-gray-500">Answer the assessment questions with "Yes" or "No", <span class="font-bold">Please note:</span> if all three questions are answered "Yes", the algorithm must be designated as high-impact and included in the algorithm register.</p>',

        'step_validation_title' => 'Information about Validation',
        'step_validation_info' => '
            <p class="text-sm text-gray-500">The answers to the assessment questions have been checked by the product owner; the aim is to ensure that the classification has been validated.</p>',

        'step_attachments_title' => 'Information about Documents & Attachments',
        'step_attachments_info' => '
            <p class="text-sm text-gray-500">If you add documents, they may also be draft versions. State clearly what kind of document it is and whether it is a draft or final. Adding, changing or deleting documents does not require a new version of the processing activity. Add remarks for clarification.</p>',
    ],

    'data_breach_record' => [
        'step_name_title' => 'Information about the Name of the Personal Data Breach',
        'step_name_info' => '
            <p class="text-sm text-gray-500">Enter here the name of the personal data breach as it is known within your organisation.</p>',

        'step_responsible_title' => 'Information about the Controller',
        'step_responsible_info' => '
            <p class="text-sm text-gray-500">Under the GDPR the contact person of the controller is: “the natural or legal person, public authority, agency or other body which, alone or jointly with others, determines the purposes and means of the processing of personal data” (Art. 4(7) GDPR).</p>',
        'step_responsible_extra_info' => '
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Choose the contact person of the controller from the list</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Select the contact person of the controller from the predefined list. Then enter the name and contact details. Choose the Data Protection Officer (DPO) from the same list. Enter the reference number from the GDPR register, if available. This field is optional.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4"><span class="font-bold">Please note:</span> Store the processor agreement under "Attachments".</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Add several controllers if necessary, e.g. for partnerships. Click [+], select the controller and repeat. Add manually if not in the list; contact the Local Functional Administrator for assistance.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4"><span class="font-bold">Please note:</span> Only enter controllers on whose behalf you process personal data.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">General information about the controller</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">The controller may be a legal person, public authority, agency or other body. A party is a controller if it determines the<span class="font-bold">purposes</span> and the<span class="font-bold">means</span> of the processing of personal data.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">A party is a controller where it determines the purposes and the means of the processing of personal data.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">A party can be a controller because:</p>
            <ul class="list-disc list-outside mb-4 ml-5">
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">The factual circumstances (conduct) lead to this. Helpful questions in that case are: Why does this processing take place? Who initiated it?</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">It has been laid down as such in law, in a decision of a supervisory authority (such as the Dutch Data Protection Authority (Autoriteit Persoonsgegevens)) or in a contract</li>
            </ul>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4"><span class="font-bold">Please note:</span> Factual circumstances (conduct) carry more weight than a contract when determining controllership.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Processor agreement</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">A contract (Art. 28(3) GDPR) must be concluded between the controller and the processor. Store it under "Attachments" in this GDPR register.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Allocation of responsibility between parties</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Where there is more than one controller, fill in the allocation between them once.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4"><span class="font-bold">Please note:</span> As a processor you have no influence on this and you are not obliged to include it in the register. Add it if it is available.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">In terms of the allocation of responsibility, the following variants are possible:</p>
            <ul class="list-disc list-outside mb-4 ml-5">
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1"><span class="font-bold">Joint controllership:</span><br> Several parties jointly determine the purposes and means. A mandatory arrangement between them is required (Article 26 GDPR).</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1"><span class="font-bold">Separate controllership:</span><br> This is the case where parties work together but each determines the purposes and means itself.</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1"><span class="font-bold">A combination of both:</span><br> It may be that a single processing activity involves both joint and separate responsibility.</li>
            </ul>',

        'step_dates_title' => 'Information about Dates',
        'step_dates_info' => '
            <p class="text-sm text-gray-500">Under "Date the personal data breach was discovered", enter the date on which the personal data breach was discovered. Under "(Presumed) start date of the breach", note when the breach presumably began, and under "End date of the breach" when it ended. Under "Date of notification to the AP", state the date on which the personal data breach was notified to the Dutch Data Protection Authority. Under "Date of completion", state the date on which the handling of the personal data breach was completed.</p>',

        'step_incident_title' => 'Information about the Incident',
        'step_incident_info' => '
            <p class="text-sm text-gray-500">Under "Nature of the incident", describe the nature of the personal data breach. Give a short "Summary of the incident". Under "Group(s) of people involved", state which groups of people are involved. Note the "Categories of personal data" and the "Special categories of personal data" that were breached. Give a "Risk assessment" of the incident. Describe the "Measures" that have been taken. State whether the incident has been "Communicated to the data subject". Note the "Means of communication used to inform the data subject". Also state whether the incident has been "Reported to the DPO" (Data Protection Officer).</p>',

        'step_notification_title' => 'Information about Notification to the AP',
        'step_notification_info' => '
            <p class="text-sm text-gray-500">This step holds the questions the Dutch Data Protection Authority asks in its online notification form and that are recorded nowhere else in the register. Fill them in if you notify the personal data breach to the AP; they then appear in the AP notification preparation. If you do not notify, you do not need to complete this step.</p>',

        'step_processing_records_title' => 'Information about Processing Activities',
        'step_processing_records_info' => '
            <p class="text-sm text-gray-500">Link the personal data breach here to processing activities from the three records of processing activities. The tables at the bottom of this page show the processing activities this personal data breach is linked to, and you can navigate to them.</p>',

        'step_attachments_title' => 'Information about Documents & Attachments',
        'step_attachments_info' => '
            <p class="text-sm text-gray-500">If you add documents, they may also be draft versions. State clearly what kind of document it is and whether it is a draft or final. Adding, changing or deleting documents does not require a new version of the processing activity. Add remarks for clarification.</p>',
    ],

    'dpia_record' => [
        'step_general_title' => 'About this DPIA',
        'step_general_info' => '
            <p class="text-sm text-gray-500">A DPIA sets out which risks a processing activity poses to the rights and freedoms of data subjects, and which measures limit those risks. This guide follows the Model DPIA Rijksdienst v3.0 (the Dutch central government DPIA model).</p>',
        'step_general_extra_info' => '
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Do this early in the process</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Carry out the DPIA at an early stage of the policy or project development. At that point it is still possible to adjust the proposal without major adverse consequences. In the case of a procurement this means: before the procurement. In the case of legislation: before the internet consultation.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Do not do it alone</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Preferably discuss the points as a group, with expertise in the policy area, legislation, (information) security and ICT. In any event involve someone with privacy expertise.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">It is not a one-way street</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300">Producing a DPIA is a dynamic process. If the risks and measures show that the processing must change, adjust the earlier sections as well so that they continue to reflect reality.</p>',

        'part_a_title' => 'Part A: the facts',
        'part_a_info' => '
            <p class="text-sm text-gray-500">Part A describes the relevant facts of the processing activities. If the facts are unclear, this carries through into the whole assessment that follows.</p>',

        'part_b_title' => 'Part B: lawfulness',
        'part_b_info' => '
            <p class="text-sm text-gray-500">Part B assesses whether the processing activities you described in part A are lawful: is there a legal basis, are they necessary and proportionate, and can data subjects exercise their rights?</p>',

        'part_c_title' => "Part C: the risks",
        'part_c_info' => '
            <p class="text-sm text-gray-500">Describe and assess the risks to the rights and freedoms of data subjects. Take account of the nature, scope, context and purposes of the processing activities.</p>',
        'part_c_extra_info' => '
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Broader than privacy alone</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">This concerns all rights and freedoms, not only the right to privacy. The model explicitly mentions the prohibition of discrimination as an example. Think also of freedom of expression, the right to a fair trial or access to services.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Likelihood x impact</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300">Determine the likelihood and the impact for each risk, and derive the risk level from them. The matrix is illustrative: you may deviate with reasons. For example, the impact of ransomware may be high, while the likelihood is very low because of technical measures.</p>',

        'part_d_title' => 'Part D: the measures',
        'part_d_info' => '
            <p class="text-sm text-gray-500">Describe which technical, organisational and legal measures prevent or reduce the risks, and which risk remains afterwards.</p>',
        'part_d_extra_info' => '
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Link measures to risks</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">The model explicitly requires a description of which measure addresses which risk. For each measure, therefore, choose the risks you address with it.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Does a high risk remain?</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300">If you cannot reduce the residual risk to an acceptable level, the Dutch Data Protection Authority (Autoriteit Persoonsgegevens) must be consulted in advance (Article 36 GDPR). Allow eight weeks, with an extension of up to six weeks.</p>',

        'step_consultation_title' => 'Consultation and advice',
        'step_consultation_info' => '
            <p class="text-sm text-gray-500">Record who has been consulted and what has been done with their advice. The advice of the data protection officer is mandatory.</p>',
        'step_consultation_extra_info' => '
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">The DPO</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Under Article 35(2) GDPR it is mandatory to seek advice from the DPO. Involve the DPO as early as possible and do not wait until the report is completely finished.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Data subjects</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300">Where appropriate, consult the data subjects or their representatives. Where it concerns personal data of your own staff, the works council must be involved. If no consultation takes place, motivate that decision in the report.</p>',

        'step_review_title' => 'Adoption and review',
        'step_review_info' => '
            <p class="text-sm text-gray-500">A DPIA is not a one-off document. Review the DPIA if the processing changes, and in any event every three years.</p>',

        'step_relations_title' => 'Processing activities and systems',
        'step_relations_info' => '
            <p class="text-sm text-gray-500">Link the processing activities and systems to which this DPIA relates. A single DPIA may cover a series of similar processing activities that present similar risks.</p>',
    ],

    'dpia_prescan_record' => [
        'step_general_title' => 'About the pre-scan',
        'step_general_info' => '
            <p class="text-sm text-gray-500">With the pre-scan you determine whether a DPIA is needed, and whether a DTIA, KIA or IAMA also comes into play. It is a threshold assessment: filling it in takes little time and the outcome can be substantiated.</p>',
        'step_general_extra_info' => '
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Keep a negative outcome as well</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">If the pre-scan shows that no DPIA is needed, that assessment must be recorded in writing with its substantiation and archived. That is why a pre-scan without a follow-up also remains as a record.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">In case of doubt</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300">If a processing activity touches on major political, administrative or societal issues, a DPIA is always desirable. If in doubt, contact a privacy officer or the DPO.</p>',

        'step_outcome_title' => 'Outcome',
        'step_outcome_info' => '
            <p class="text-sm text-gray-500">The outcome follows automatically from your answers. For each assessment you see not only what the outcome is, but also why, so that the conclusion remains traceable.</p>',
    ],
];
