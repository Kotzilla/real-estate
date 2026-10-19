<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Thai copies of Laravel's validation messages (same keys as lang/en/validation.php). APP_FALLBACK_LOCALE is th,
    | so a key missing here shows as "validation.xxx": keep both files in step.
    |
    */

    'accepted' => 'กรุณายอมรับ :attribute',
    'accepted_if' => 'กรุณายอมรับ :attribute เมื่อ :other เป็น :value',
    'active_url' => ':attribute ต้องเป็น URL ที่ใช้งานได้',
    'after' => ':attribute ต้องเป็นวันที่หลัง :date',
    'after_or_equal' => ':attribute ต้องเป็นวันที่ :date หรือหลังจากนั้น',
    'alpha' => ':attribute ต้องมีเฉพาะตัวอักษร',
    'alpha_dash' => ':attribute ต้องมีเฉพาะตัวอักษร ตัวเลข ขีดกลาง และขีดล่าง',
    'alpha_num' => ':attribute ต้องมีเฉพาะตัวอักษรและตัวเลข',
    'any_of' => ':attribute ไม่ถูกต้อง',
    'array' => ':attribute ต้องเป็นรายการ',
    'ascii' => ':attribute ต้องมีเฉพาะตัวอักษรภาษาอังกฤษ ตัวเลข และสัญลักษณ์',
    'before' => ':attribute ต้องเป็นวันที่ก่อน :date',
    'before_or_equal' => ':attribute ต้องเป็นวันที่ :date หรือก่อนหน้านั้น',
    'between' => [
        'array' => ':attribute ต้องมี :min ถึง :max รายการ',
        'file' => ':attribute ต้องมีขนาด :min ถึง :max กิโลไบต์',
        'numeric' => ':attribute ต้องอยู่ระหว่าง :min ถึง :max',
        'string' => ':attribute ต้องมี :min ถึง :max ตัวอักษร',
    ],
    'boolean' => ':attribute ต้องเป็นจริงหรือเท็จ',
    'can' => ':attribute มีค่าที่ไม่ได้รับอนุญาต',
    'confirmed' => 'การยืนยัน :attribute ไม่ตรงกัน',
    'contains' => ':attribute ขาดค่าที่จำเป็น',
    'current_password' => 'รหัสผ่านไม่ถูกต้อง',
    'date' => ':attribute ต้องเป็นวันที่ที่ถูกต้อง',
    'date_equals' => ':attribute ต้องเป็นวันที่ :date',
    'date_format' => ':attribute ต้องอยู่ในรูปแบบ :format',
    'decimal' => ':attribute ต้องมีทศนิยม :decimal ตำแหน่ง',
    'declined' => 'กรุณาปฏิเสธ :attribute',
    'declined_if' => 'กรุณาปฏิเสธ :attribute เมื่อ :other เป็น :value',
    'different' => ':attribute และ :other ต้องไม่เหมือนกัน',
    'digits' => ':attribute ต้องเป็นตัวเลข :digits หลัก',
    'digits_between' => ':attribute ต้องเป็นตัวเลข :min ถึง :max หลัก',
    'dimensions' => 'ขนาดรูปภาพของ :attribute ไม่ถูกต้อง',
    'distinct' => ':attribute มีค่าซ้ำกัน',
    'doesnt_contain' => ':attribute ต้องไม่มีค่าเหล่านี้: :values',
    'doesnt_end_with' => ':attribute ต้องไม่ลงท้ายด้วย: :values',
    'doesnt_start_with' => ':attribute ต้องไม่ขึ้นต้นด้วย: :values',
    'email' => ':attribute ต้องเป็นอีเมลที่ถูกต้อง',
    'encoding' => ':attribute ต้องเข้ารหัสแบบ :encoding',
    'ends_with' => ':attribute ต้องลงท้ายด้วย: :values',
    'enum' => ':attribute ที่เลือกไม่ถูกต้อง',
    'exists' => ':attribute ที่เลือกไม่ถูกต้อง',
    'extensions' => ':attribute ต้องเป็นไฟล์นามสกุล: :values',
    'file' => ':attribute ต้องเป็นไฟล์',
    'filled' => 'กรุณากรอก :attribute',
    'gt' => [
        'array' => ':attribute ต้องมีมากกว่า :value รายการ',
        'file' => ':attribute ต้องมีขนาดมากกว่า :value กิโลไบต์',
        'numeric' => ':attribute ต้องมากกว่า :value',
        'string' => ':attribute ต้องมีมากกว่า :value ตัวอักษร',
    ],
    'gte' => [
        'array' => ':attribute ต้องมีอย่างน้อย :value รายการ',
        'file' => ':attribute ต้องมีขนาดอย่างน้อย :value กิโลไบต์',
        'numeric' => ':attribute ต้องมากกว่าหรือเท่ากับ :value',
        'string' => ':attribute ต้องมีอย่างน้อย :value ตัวอักษร',
    ],
    'hex_color' => ':attribute ต้องเป็นรหัสสีฐานสิบหกที่ถูกต้อง',
    'image' => ':attribute ต้องเป็นรูปภาพ',
    'in' => ':attribute ที่เลือกไม่ถูกต้อง',
    'in_array' => ':attribute ต้องอยู่ใน :other',
    'in_array_keys' => ':attribute ต้องมีอย่างน้อยหนึ่งคีย์ต่อไปนี้: :values',
    'integer' => ':attribute ต้องเป็นจำนวนเต็ม',
    'ip' => ':attribute ต้องเป็นที่อยู่ IP ที่ถูกต้อง',
    'ipv4' => ':attribute ต้องเป็นที่อยู่ IPv4 ที่ถูกต้อง',
    'ipv6' => ':attribute ต้องเป็นที่อยู่ IPv6 ที่ถูกต้อง',
    'json' => ':attribute ต้องเป็น JSON ที่ถูกต้อง',
    'list' => ':attribute ต้องเป็นรายการ',
    'lowercase' => ':attribute ต้องเป็นตัวพิมพ์เล็ก',
    'lt' => [
        'array' => ':attribute ต้องมีน้อยกว่า :value รายการ',
        'file' => ':attribute ต้องมีขนาดน้อยกว่า :value กิโลไบต์',
        'numeric' => ':attribute ต้องน้อยกว่า :value',
        'string' => ':attribute ต้องมีน้อยกว่า :value ตัวอักษร',
    ],
    'lte' => [
        'array' => ':attribute ต้องมีไม่เกิน :value รายการ',
        'file' => ':attribute ต้องมีขนาดไม่เกิน :value กิโลไบต์',
        'numeric' => ':attribute ต้องน้อยกว่าหรือเท่ากับ :value',
        'string' => ':attribute ต้องมีไม่เกิน :value ตัวอักษร',
    ],
    'mac_address' => ':attribute ต้องเป็นที่อยู่ MAC ที่ถูกต้อง',
    'max' => [
        'array' => ':attribute ต้องมีไม่เกิน :max รายการ',
        'file' => ':attribute ต้องมีขนาดไม่เกิน :max กิโลไบต์',
        'numeric' => ':attribute ต้องไม่เกิน :max',
        'string' => ':attribute ต้องมีไม่เกิน :max ตัวอักษร',
    ],
    'max_digits' => ':attribute ต้องมีไม่เกิน :max หลัก',
    'mimes' => ':attribute ต้องเป็นไฟล์ประเภท: :values',
    'mimetypes' => ':attribute ต้องเป็นไฟล์ประเภท: :values',
    'min' => [
        'array' => ':attribute ต้องมีอย่างน้อย :min รายการ',
        'file' => ':attribute ต้องมีขนาดอย่างน้อย :min กิโลไบต์',
        'numeric' => ':attribute ต้องมีค่าอย่างน้อย :min',
        'string' => ':attribute ต้องมีอย่างน้อย :min ตัวอักษร',
    ],
    'min_digits' => ':attribute ต้องมีอย่างน้อย :min หลัก',
    'missing' => 'ต้องไม่มี :attribute',
    'missing_if' => 'ต้องไม่มี :attribute เมื่อ :other เป็น :value',
    'missing_unless' => 'ต้องไม่มี :attribute เว้นแต่ :other เป็น :value',
    'missing_with' => 'ต้องไม่มี :attribute เมื่อมี :values',
    'missing_with_all' => 'ต้องไม่มี :attribute เมื่อมี :values ทั้งหมด',
    'multiple_of' => ':attribute ต้องเป็นผลคูณของ :value',
    'not_in' => ':attribute ที่เลือกไม่ถูกต้อง',
    'not_regex' => 'รูปแบบของ :attribute ไม่ถูกต้อง',
    'numeric' => ':attribute ต้องเป็นตัวเลข',
    'password' => [
        'letters' => ':attribute ต้องมีตัวอักษรอย่างน้อยหนึ่งตัว',
        'mixed' => ':attribute ต้องมีทั้งตัวพิมพ์ใหญ่และตัวพิมพ์เล็กอย่างน้อยอย่างละหนึ่งตัว',
        'numbers' => ':attribute ต้องมีตัวเลขอย่างน้อยหนึ่งตัว',
        'symbols' => ':attribute ต้องมีสัญลักษณ์อย่างน้อยหนึ่งตัว',
        'uncompromised' => ':attribute นี้เคยรั่วไหลในเหตุข้อมูลรั่ว กรุณาเลือก :attribute อื่น',
    ],
    'present' => 'ต้องมี :attribute',
    'present_if' => 'ต้องมี :attribute เมื่อ :other เป็น :value',
    'present_unless' => 'ต้องมี :attribute เว้นแต่ :other เป็น :value',
    'present_with' => 'ต้องมี :attribute เมื่อมี :values',
    'present_with_all' => 'ต้องมี :attribute เมื่อมี :values ทั้งหมด',
    'prohibited' => 'ไม่อนุญาตให้มี :attribute',
    'prohibited_if' => 'ไม่อนุญาตให้มี :attribute เมื่อ :other เป็น :value',
    'prohibited_if_accepted' => 'ไม่อนุญาตให้มี :attribute เมื่อยอมรับ :other',
    'prohibited_if_declined' => 'ไม่อนุญาตให้มี :attribute เมื่อปฏิเสธ :other',
    'prohibited_unless' => 'ไม่อนุญาตให้มี :attribute เว้นแต่ :other อยู่ใน :values',
    'prohibits' => ':attribute ไม่อนุญาตให้มี :other',
    'regex' => 'รูปแบบของ :attribute ไม่ถูกต้อง',
    'required' => 'กรุณากรอก :attribute',
    'required_array_keys' => ':attribute ต้องมีข้อมูลของ: :values',
    'required_if' => 'กรุณากรอก :attribute เมื่อ :other เป็น :value',
    'required_if_accepted' => 'กรุณากรอก :attribute เมื่อยอมรับ :other',
    'required_if_declined' => 'กรุณากรอก :attribute เมื่อปฏิเสธ :other',
    'required_unless' => 'กรุณากรอก :attribute เว้นแต่ :other อยู่ใน :values',
    'required_with' => 'กรุณากรอก :attribute เมื่อมี :values',
    'required_with_all' => 'กรุณากรอก :attribute เมื่อมี :values ทั้งหมด',
    'required_without' => 'กรุณากรอก :attribute เมื่อไม่มี :values',
    'required_without_all' => 'กรุณากรอก :attribute เมื่อไม่มี :values เลย',
    'same' => ':attribute และ :other ต้องตรงกัน',
    'size' => [
        'array' => ':attribute ต้องมี :size รายการ',
        'file' => ':attribute ต้องมีขนาด :size กิโลไบต์',
        'numeric' => ':attribute ต้องเท่ากับ :size',
        'string' => ':attribute ต้องมี :size ตัวอักษร',
    ],
    'starts_with' => ':attribute ต้องขึ้นต้นด้วย: :values',
    'string' => ':attribute ต้องเป็นข้อความ',
    'timezone' => ':attribute ต้องเป็นเขตเวลาที่ถูกต้อง',
    'unique' => ':attribute นี้ถูกใช้แล้ว',
    'uploaded' => 'อัปโหลด :attribute ไม่สำเร็จ',
    'uppercase' => ':attribute ต้องเป็นตัวพิมพ์ใหญ่',
    'url' => ':attribute ต้องเป็น URL ที่ถูกต้อง',
    'ulid' => ':attribute ต้องเป็น ULID ที่ถูกต้อง',
    'uuid' => ':attribute ต้องเป็น UUID ที่ถูกต้อง',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    */

    'attributes' => [],

];
