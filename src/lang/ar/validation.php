 <?php

    return [

        /*
    |--------------------------------------------------------------------------
    | أسطر لغة التحقق
    |--------------------------------------------------------------------------
    |
    | تحتوي أسطر اللغة التالية على رسائل الخطأ الافتراضية المستخدمة
    | بواسطة نظام التحقق في Laravel.
    |
    */

        'accepted' => 'يجب قبول حقل :attribute.',
        'accepted_if' => 'يجب قبول حقل :attribute عندما تكون قيمة :other هي :value.',
        'active_url' => 'يجب أن يكون حقل :attribute عنوان URL صالحًا.',
        'after' => 'يجب أن يكون تاريخ حقل :attribute بعد :date.',
        'after_or_equal' => 'يجب أن يكون تاريخ حقل :attribute بعد أو مساويًا لـ :date.',
        'alpha' => 'يجب أن يحتوي حقل :attribute على أحرف فقط.',
        'alpha_dash' => 'يجب أن يحتوي حقل :attribute على أحرف وأرقام وشرطات وشرطات سفلية فقط.',
        'alpha_num' => 'يجب أن يحتوي حقل :attribute على أحرف وأرقام فقط.',
        'any_of' => 'قيمة حقل :attribute غير صالحة.',
        'array' => 'يجب أن يكون حقل :attribute عبارة عن مصفوفة.',
        'ascii' => 'يجب أن يحتوي حقل :attribute على أحرف وأرقام ورموز من نوع ASCII فقط.',
        'before' => 'يجب أن يكون تاريخ حقل :attribute قبل :date.',
        'before_or_equal' => 'يجب أن يكون تاريخ حقل :attribute قبل أو مساويًا لـ :date.',

        'between' => [
            'array' => 'يجب أن يحتوي حقل :attribute على عدد من العناصر بين :min و :max.',
            'file' => 'يجب أن يكون حجم حقل :attribute بين :min و :max كيلوبايت.',
            'numeric' => 'يجب أن تكون قيمة حقل :attribute بين :min و :max.',
            'string' => 'يجب أن يكون طول حقل :attribute بين :min و :max حرفًا.',
        ],

        'boolean' => 'يجب أن تكون قيمة حقل :attribute صحيحة أو خاطئة.',
        'can' => 'يحتوي حقل :attribute على قيمة غير مصرح بها.',
        'confirmed' => 'تأكيد حقل :attribute غير متطابق.',
        'contains' => 'حقل :attribute لا يحتوي على القيمة المطلوبة.',
        'current_password' => 'كلمة المرور غير صحيحة.',
        'date' => 'يجب أن يكون حقل :attribute تاريخًا صالحًا.',
        'date_equals' => 'يجب أن يكون تاريخ حقل :attribute مساويًا لـ :date.',
        'date_format' => 'يجب أن يتوافق حقل :attribute مع التنسيق :format.',
        'decimal' => 'يجب أن يحتوي حقل :attribute على :decimal من المنازل العشرية.',
        'declined' => 'يجب رفض حقل :attribute.',
        'declined_if' => 'يجب رفض حقل :attribute عندما تكون قيمة :other هي :value.',
        'different' => 'يجب أن يكون حقل :attribute مختلفًا عن :other.',
        'digits' => 'يجب أن يتكون حقل :attribute من :digits أرقام.',
        'digits_between' => 'يجب أن يحتوي حقل :attribute على عدد من الأرقام بين :min و :max.',
        'dimensions' => 'أبعاد الصورة في حقل :attribute غير صالحة.',
        'distinct' => 'يحتوي حقل :attribute على قيمة مكررة.',
        'doesnt_contain' => 'يجب ألا يحتوي حقل :attribute على أي من القيم التالية: :values.',
        'doesnt_end_with' => 'يجب ألا ينتهي حقل :attribute بأحد القيم التالية: :values.',
        'doesnt_start_with' => 'يجب ألا يبدأ حقل :attribute بأحد القيم التالية: :values.',
        'email' => 'يجب أن يكون حقل :attribute عنوان بريد إلكتروني صالحًا.',
        'encoding' => 'يجب أن يكون حقل :attribute مشفرًا باستخدام :encoding.',
        'ends_with' => 'يجب أن ينتهي حقل :attribute بأحد القيم التالية: :values.',
        'enum' => 'القيمة المحددة لحقل :attribute غير صالحة.',
        'exists' => 'القيمة المحددة لحقل :attribute غير صالحة.',
        'extensions' => 'يجب أن يكون امتداد ملف :attribute أحد الامتدادات التالية: :values.',
        'file' => 'يجب أن يكون حقل :attribute ملفًا.',
        'filled' => 'يجب أن يحتوي حقل :attribute على قيمة.',

        'gt' => [
            'array' => 'يجب أن يحتوي حقل :attribute على أكثر من :value من العناصر.',
            'file' => 'يجب أن يكون حجم حقل :attribute أكبر من :value كيلوبايت.',
            'numeric' => 'يجب أن تكون قيمة حقل :attribute أكبر من :value.',
            'string' => 'يجب أن يكون حقل :attribute أطول من :value حرفًا.',
        ],

        'gte' => [
            'array' => 'يجب أن يحتوي حقل :attribute على :value من العناصر أو أكثر.',
            'file' => 'يجب أن يكون حجم حقل :attribute أكبر من أو مساويًا لـ :value كيلوبايت.',
            'numeric' => 'يجب أن تكون قيمة حقل :attribute أكبر من أو مساوية لـ :value.',
            'string' => 'يجب أن يكون طول حقل :attribute أكبر من أو مساويًا لـ :value حرفًا.',
        ],

        'hex_color' => 'يجب أن يكون حقل :attribute لونًا سداسيًا صالحًا.',
        'image' => 'يجب أن يكون حقل :attribute صورة.',
        'in' => 'القيمة المحددة لحقل :attribute غير صالحة.',
        'in_array' => 'يجب أن تكون قيمة حقل :attribute موجودة في :other.',
        'in_array_keys' => 'يجب أن يحتوي حقل :attribute على مفتاح واحد على الأقل من المفاتيح التالية: :values.',
        'integer' => 'يجب أن يكون حقل :attribute عددًا صحيحًا.',
        'ip' => 'يجب أن يكون حقل :attribute عنوان IP صالحًا.',
        'ipv4' => 'يجب أن يكون حقل :attribute عنوان IPv4 صالحًا.',
        'ipv6' => 'يجب أن يكون حقل :attribute عنوان IPv6 صالحًا.',
        'json' => 'يجب أن يكون حقل :attribute نص JSON صالحًا.',
        'list' => 'يجب أن يكون حقل :attribute قائمة.',
        'lowercase' => 'يجب أن يكون حقل :attribute بأحرف صغيرة.',

        'lt' => [
            'array' => 'يجب أن يحتوي حقل :attribute على أقل من :value من العناصر.',
            'file' => 'يجب أن يكون حجم حقل :attribute أقل من :value كيلوبايت.',
            'numeric' => 'يجب أن تكون قيمة حقل :attribute أقل من :value.',
            'string' => 'يجب أن يكون طول حقل :attribute أقل من :value حرفًا.',
        ],

        'lte' => [
            'array' => 'يجب ألا يحتوي حقل :attribute على أكثر من :value من العناصر.',
            'file' => 'يجب ألا يتجاوز حجم حقل :attribute :value كيلوبايت.',
            'numeric' => 'يجب ألا تتجاوز قيمة حقل :attribute :value.',
            'string' => 'يجب ألا يتجاوز طول حقل :attribute :value حرفًا.',
        ],

        'mac_address' => 'يجب أن يكون حقل :attribute عنوان MAC صالحًا.',

        'max' => [
            'array' => 'يجب ألا يحتوي حقل :attribute على أكثر من :max من العناصر.',
            'file' => 'يجب ألا يتجاوز حجم حقل :attribute :max كيلوبايت.',
            'numeric' => 'يجب ألا تتجاوز قيمة حقل :attribute :max.',
            'string' => 'يجب ألا يتجاوز طول حقل :attribute :max حرفًا.',
        ],

        'max_digits' => 'يجب ألا يحتوي حقل :attribute على أكثر من :max أرقام.',
        'mimes' => 'يجب أن يكون ملف :attribute من أحد الأنواع التالية: :values.',
        'mimetypes' => 'يجب أن يكون ملف :attribute من أحد الأنواع التالية: :values.',

        'min' => [
            'array' => 'يجب أن يحتوي حقل :attribute على :min من العناصر على الأقل.',
            'file' => 'يجب ألا يقل حجم حقل :attribute عن :min كيلوبايت.',
            'numeric' => 'يجب ألا تقل قيمة حقل :attribute عن :min.',
            'string' => 'يجب ألا يقل طول حقل :attribute عن :min حرفًا.',
        ],

        'min_digits' => 'يجب أن يحتوي حقل :attribute على :min أرقام على الأقل.',
        'missing' => 'يجب ألا يكون حقل :attribute موجودًا.',
        'missing_if' => 'يجب ألا يكون حقل :attribute موجودًا عندما تكون قيمة :other هي :value.',
        'missing_unless' => 'يجب ألا يكون حقل :attribute موجودًا إلا إذا كانت قيمة :other هي :value.',
        'missing_with' => 'يجب ألا يكون حقل :attribute موجودًا عند وجود :values.',
        'missing_with_all' => 'يجب ألا يكون حقل :attribute موجودًا عند وجود جميع القيم: :values.',
        'multiple_of' => 'يجب أن تكون قيمة حقل :attribute من مضاعفات :value.',
        'not_in' => 'القيمة المحددة لحقل :attribute غير صالحة.',
        'not_regex' => 'تنسيق حقل :attribute غير صالح.',
        'numeric' => 'يجب أن يكون حقل :attribute رقمًا.',

        'password' => [
            'letters' => 'يجب أن يحتوي حقل :attribute على حرف واحد على الأقل.',
            'mixed' => 'يجب أن يحتوي حقل :attribute على حرف كبير وحرف صغير على الأقل.',
            'numbers' => 'يجب أن يحتوي حقل :attribute على رقم واحد على الأقل.',
            'symbols' => 'يجب أن يحتوي حقل :attribute على رمز واحد على الأقل.',
            'uncompromised' => 'ظهرت قيمة :attribute في تسريب للبيانات. يرجى اختيار قيمة مختلفة لـ :attribute.',
        ],

        'present' => 'يجب أن يكون حقل :attribute موجودًا.',
        'present_if' => 'يجب أن يكون حقل :attribute موجودًا عندما تكون قيمة :other هي :value.',
        'present_unless' => 'يجب أن يكون حقل :attribute موجودًا إلا إذا كانت قيمة :other هي :value.',
        'present_with' => 'يجب أن يكون حقل :attribute موجودًا عند وجود :values.',
        'present_with_all' => 'يجب أن يكون حقل :attribute موجودًا عند وجود جميع القيم: :values.',

        'prohibited' => 'حقل :attribute غير مسموح به.',
        'prohibited_if' => 'حقل :attribute غير مسموح به عندما تكون قيمة :other هي :value.',
        'prohibited_if_accepted' => 'حقل :attribute غير مسموح به عندما تكون قيمة :other مقبولة.',
        'prohibited_if_declined' => 'حقل :attribute غير مسموح به عندما تكون قيمة :other مرفوضة.',
        'prohibited_unless' => 'حقل :attribute غير مسموح به إلا إذا كانت قيمة :other ضمن :values.',
        'prohibits' => 'يمنع حقل :attribute وجود :other.',

        'regex' => 'تنسيق حقل :attribute غير صالح.',
        'required' => 'حقل :attribute مطلوب.',
        'required_array_keys' => 'يجب أن يحتوي حقل :attribute على القيم التالية: :values.',
        'required_if' => 'حقل :attribute مطلوب عندما تكون قيمة :other هي :value.',
        'required_if_accepted' => 'حقل :attribute مطلوب عندما تكون قيمة :other مقبولة.',
        'required_if_declined' => 'حقل :attribute مطلوب عندما تكون قيمة :other مرفوضة.',
        'required_unless' => 'حقل :attribute مطلوب إلا إذا كانت قيمة :other ضمن :values.',
        'required_with' => 'حقل :attribute مطلوب عند وجود :values.',
        'required_with_all' => 'حقل :attribute مطلوب عند وجود جميع القيم: :values.',
        'required_without' => 'حقل :attribute مطلوب عند عدم وجود :values.',
        'required_without_all' => 'حقل :attribute مطلوب عند عدم وجود أي من القيم: :values.',

        'same' => 'يجب أن يتطابق حقل :attribute مع :other.',

        'size' => [
            'array' => 'يجب أن يحتوي حقل :attribute على :size من العناصر.',
            'file' => 'يجب أن يكون حجم حقل :attribute :size كيلوبايت.',
            'numeric' => 'يجب أن تكون قيمة حقل :attribute :size.',
            'string' => 'يجب أن يكون طول حقل :attribute :size حرفًا.',
        ],

        'starts_with' => 'يجب أن يبدأ حقل :attribute بأحد القيم التالية: :values.',
        'string' => 'يجب أن يكون حقل :attribute نصًا.',
        'timezone' => 'يجب أن يكون حقل :attribute منطقة زمنية صالحة.',
        'unique' => 'قيمة :attribute مستخدمة بالفعل.',
        'uploaded' => 'فشل تحميل :attribute.',
        'uppercase' => 'يجب أن يكون حقل :attribute بأحرف كبيرة.',
        'url' => 'يجب أن يكون حقل :attribute عنوان URL صالحًا.',
        'ulid' => 'يجب أن يكون حقل :attribute معرّف ULID صالحًا.',
        'uuid' => 'يجب أن يكون حقل :attribute معرّف UUID صالحًا.',

        /*
    |--------------------------------------------------------------------------
    | أسطر لغة التحقق المخصصة
    |--------------------------------------------------------------------------
    */

        'custom' => [
            'attribute-name' => [
                'rule-name' => 'رسالة مخصصة.',
            ],
        ],

        /*
    |--------------------------------------------------------------------------
    | أسماء الحقول المخصصة
    |--------------------------------------------------------------------------
    */

        'attributes' => [],

    ];
