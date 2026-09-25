<?php
/**
 * =====================================================
 *  إعدادات قاعدة البيانات - واحة المنصورية
 *
 *  ✅ على جهازك المحلي: اترك DB_DRIVER على 'sqlite' (بدون أي إعداد)
 *
 *  ✅ على استضافة مجانية (InfinityFree وأمثالها) استخدم MySQL:
 *  1) أنشئ قاعدة MySQL من لوحة الاستضافة (Control Panel => MySQL Databases)
 *  2) غيّر DB_DRIVER إلى 'mysql'
 *  3) املأ البيانات الأربعة (هتلاقيها كلها في صفحة MySQL Databases:
 *     MySQL Host Name / Database Name / Username / كلمة المرور اللي اخترتها)
 *  4) النظام ينشئ الجداول والبيانات الأولية تلقائيًا أول ما يفتح
 * =====================================================
 */

define('DB_DRIVER', 'sqlite');            // 'sqlite' أو 'mysql'

// --- بيانات MySQL (تُستخدم فقط عندما DB_DRIVER = 'mysql') ---
define('DB_HOST', 'sqlXXX.infinityfree.com');   // عنوان سيرفر MySQL
define('DB_NAME', 'if0_XXXXXXXX_wahat');       // اسم قاعدة البيانات
define('DB_USER', 'if0_XXXXXXXX');             // اسم المستخدم
define('DB_PASS', 'كلمة_مرور_القاعدة');        // كلمة المرور
