# SEO والروابط العامة — HAG Brokers

## ما تنفذه هذه المرحلة

1. عمود slug فريد للفرص، مع إبقاء IDs والعلاقات المالية كما هي. رابط عام مثل /opportunities/office-chairs-in-dubai-k4p9w2m7rx؛ العناوين العربية تحتفظ بكلماتها العربية. الجزء العشوائي القصير يضمن تمييز العناوين المتكررة دون عرض ID.
2. إنشاء slugs للفرص السابقة بإضافة حقول فقط؛ لا حذف أو إعادة seeding أو تغيير أسعار أو حالة تحقق أو ملكية. تحديث العنوان لا يغير الرابط. الربط الإداري وروابط تعديل الشركة يبقيان بالأرقام.
3. روابط GET الرقمية القديمة تتحول 301 إلى slug بعد التحقق من أن الفرصة عامة ومؤهلة. الخاصة والمغلقة والمنتهية ترجع 404 دون كشف slug. POST القديم للتقديم لا يتحول كي لا يفقد محتواه، لكنه يبقى تحت التحقق والتفويض الحاليين.
4. عنوان ووصف وcanonical وOpen Graph/Twitter وJSON-LD آمنة، مع مسار تنقل. البيانات المنظمة تصف Organization/WebSite/WebPage/BreadcrumbList؛ لا تقييمات أو شهادات أو Product/Offer مختلقة.
5. صفحات لغة ثابتة ?lang=ar و?lang=en للرئيسية والتصفح والتفاصيل، مع canonical ذاتي وhreflang متبادل وx-default. الوصول لهذه النسخ لا يتطلب اختيار جلسة سابقًا. المحتوى الذي تكتبه الشركة لا يُترجم تلقائيًا؛ لتحسين المحتوى الإنجليزي الفعلي يلزم وصف إنجليزي حقيقي أيضًا.
6. noindex للصفحات الخاصة والمصادقة ونتائج البحث/التصفية. صفحات التصفح غير المفلترة تبقى متاحة، والصفحات المرقمة لها canonical صحيح.
7. /sitemap.xml فهرس ديناميكي يقود إلى /sitemaps/pages.xml و/sitemaps/opportunities-1.xml وما بعدها. كل جزء بحد أقصى 1000 فرصة/2000 رابط لغة. لا تظهر الروابط الرقمية أو صفحات الحسابات أو الفرص غير العامة.
8. عند إغلاق فرصة أو انتهائها أو تعليق الشركة تتوقف عن الظهور في sitemap مباشرة دون cron أو ملف قديم مخزن. قد تختلف أجزاء الخريطة أثناء تغييرات متزامنة؛ الطلب اللاحق يقرأ الحالة الحالية.
9. lastmod من seo_updated_at للتعديلات العامة عبر Eloquent فقط؛ الملاحظات الخاصة لا تغيّره. لا يُختلق lastmod للسجلات السابقة أثناء تعبئة slug، ويُحذف الوسم عندما يكون التاريخ غير معروف. الكتابة المباشرة إلى DB خارج النماذج لا تحدّث هذا التاريخ تلقائيًا.
10. robots.txt ديناميكي يعلن رابط sitemap من الإعداد الموثوق، وأمر seo:check للقراءة فقط. يُحذف الملف الثابت public/robots.txt بعد حفظه في النسخة الاحتياطية حتى لا يحجب المسار الديناميكي. إذا كان ملفك الثابت معدلًا محليًا يتوقف المثبت بدل حذفه.

لا توجد ضمانة تصدر أو فهرسة من slug أو sitemap وحدهما. لا نضيف meta keywords لأن Google لا تستخدمها للترتيب، ولا نقدم صورًا أو مراجعات أو بيانات مؤسسة غير حقيقية.

## التثبيت المحلي

النسخة مبنية فوق المرحلة 11A مع تحديثات المراحل والمظهر والرئيسية. طبّقها على نسخة العمل الحالية؛ المثبت يدعم دمج التعديلات المحلية ويتوقف عند التعارض.

بعد فك الحزمة في Downloads:

```bash
cd "$HOME/Downloads/trans-marine-docker-kit" || exit 1
python3 "$HOME/Downloads/HAG_Public_SEO_Slugs/install.py" --project "$PWD" &&
python3 "$HOME/Downloads/HAG_Public_SEO_Slugs/install.py" --project "$PWD" --apply &&
git diff --check &&
docker compose exec -T app php artisan config:clear &&
docker compose exec -T app php artisan route:clear &&
docker compose exec -T app php artisan migrate &&
docker compose exec -T app php artisan test --filter='PublicSeoTest|HomeMarketplaceTest|CompanyMarketplaceTest' &&
docker compose exec -T app php artisan seo:check &&
docker compose exec -T app php artisan view:cache &&
(cd src && npm run build)
```

لا تستخدم migrate:fresh أو refresh أو reset أو db:wipe. المثبت يحفظ نسخة أمان للملفات وSQLite، ولا ينفذ migration أو restore أو seeders بنفسه. Migration واحدة تضيف slug وseo_updated_at وتملأ slug فقط في السجلات السابقة. قد يستغرق الترحيل وقتًا مع قواعد كبيرة؛ تعارضات تخص بياناتك الفعلية تحتاج الفحص قبل متابعة النشر العام.

محليًا يكون Indexing DISABLED طبيعيًا. افحص:

- http://localhost:8080/sitemap.xml
- http://localhost:8080/robots.txt
- افتح فرصة عامة من التصفح: يجب أن يكون رابطها slug.
- افتح رابطها الرقمي السابق: يجب أن يتحول 301.
- عدّل عنوانها: يجب ألا يتغير slug.
- تحقق من الاختلاف بين ?lang=ar و?lang=en ومن بقاء المراحل والمظهر.

## إعداد الدومين العام

على الاستضافة العامة فقط، اضبط src/.env أو .env حسب مكان تطبيق Laravel. اختر دومين المنصة الفعلي؛ لا تستعمل دومين WordPress آخر أو localhost. لا يلزم أي مفتاح OpenAI أو Stripe لهذه المرحلة.

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://YOUR_PUBLIC_DOMAIN
SEO_CANONICAL_URL=https://YOUR_PUBLIC_DOMAIN
SEO_INDEXING_ENABLED=true
SEO_SOCIAL_IMAGE=/storage/YOUR_REAL_SOCIAL_IMAGE.png
SEO_GOOGLE_SITE_VERIFICATION=
SEO_BING_SITE_VERIFICATION=
```

استبدل القيم النموذجية بدومينك وصورة موجودة فعلًا، أو اترك SEO_SOCIAL_IMAGE فارغًا حتى تتوفر صورة حقيقية. صورة مشاركة بنسبة 1.91:1 مثل 1200×630 مناسبة لبطاقات المشاركة، لكنها ليست ضمانة ترتيب. تستعمل الفرصة أول صورة عامة متاحة لها تلقائيًا؛ لا يرسل HTML الملاحظات الداخلية في JSON-LD.

اضبط HTTPS وإعادة توجيه الدومين/النسخة البديلة مثل www إلى النسخة الأساسية في الاستضافة. هذا التحديث يثبت canonical موثوقًا من الإعداد بدل Host في الطلب، لكنه لا يغيّر DNS أو الشهادات أو قواعد تحويل المضيف على خادمك.

بعد الإعداد شغل على السيرفر config:clear وroute:clear ثم seo:check؛ ويمكن إعادة بناء config:cache/route:cache وفق آلية نشر مشروعك. افحص فعليًا أن /robots.txt يعرض النص الجديد وSitemap بالدومين الصحيح؛ إعداد خادم يفرض ملفًا ثابتًا لـrobots قد يحتاج تعديلًا، بينما Nginx في Docker الحالي يحول الملف غير الموجود إلى Laravel.

البيئة التجريبية تبقى SEO_INDEXING_ENABLED=false. robots/noindex ليسا حماية للبيانات؛ حماية الحسابات تعتمد على التفويض والمصادقة الموجودة.

## Google Search Console

1. افتح https://search.google.com/search-console/ وأضف نطاق المنصة.
2. لإثبات Domain property أضف سجل DNS الذي توفره Google إلى لوحة دومينك. لا تتطلب هذه الطريقة رمز meta في التطبيق.
3. أو اختر URL-prefix property واستعمل رمز HTML tag: انسخ قيمة content فقط إلى SEO_GOOGLE_SITE_VERIFICATION محليًا/على السيرفر، ثم config:clear.
4. تأكد أن الصفحة العامة متاحة وأن الفهرسة مفعلة، ثم أكمل Verify في حسابك.
5. أرسل sitemap.xml من قسم Sitemaps، وليس رابط localhost.
6. استخدم URL Inspection لفحص فرصة عامة بالـslug، وراجع الاستجابة 200 وcanonical واللغة وعدم وجود noindex.
7. تابع Pages وPerformance وCore Web Vitals وراجع الصفحات المستبعدة. تأخر ظهور البيانات أو عدم الفهرسة لا يعني ضرورة إعادة إصدار الروابط.

لم تُنشأ خاصية Search Console أو تُرسل sitemap إلى حسابك تلقائيًا؛ ذلك يحتاج ملكيتك للدومين وحسابك.

## Bing وأدوات الفحص

- https://www.bing.com/webmasters/ : تحقق من الدومين وأرسل sitemap.xml، أو استورد الخاصية من Search Console وفق خيارات حسابك. يمكن وضع قيمة meta الخاصة به في SEO_BING_SITE_VERIFICATION ثم config:clear.
- https://pagespeed.web.dev/ : افحص الرئيسية وفرصة عامة على الدومين المنشور. سجّل نتائج الهاتف الفعلية ثم عالج أكبر سبب للتأخير؛ لم يُنفذ قياس حي هنا.
- https://search.google.com/test/rich-results : افحص البيانات المنظمة. قد لا تكون أنواع WebPage/Organization مؤهلة لنتيجة غنية؛ BreadcrumbList يمكن فحصه، ولا توجد ضمانة ظهور مميز.
- Lighthouse في Chrome DevTools: فحص محلي للأداء وإمكانية الوصول وSEO. قياس الأداة ليس ضمانة ترتيب.

## المحتوى والروابط: العمل المستمر بعد الأساس التقني

اكتب عناوين محددة ووصفًا أصليًا ومواصفات وشروطًا حقيقية، مع موقع وقطاع واضحين. استخدم صورًا تخص العرض، وتجنب نسخ إعلانات أو تكرار الكلمات أو نشر آلاف الصفحات الفارغة. بناء ملفات شركات موثوقة ومحتوى إرشادي أصلي وروابط مشروعة من جهات ذات صلة يحتاج عملًا لاحقًا؛ لا يمكن توليد السمعة أو العملاء بمجرد إضافة ملفات SEO. لن نرسل إخطارات جماعية أو نشتري روابط.

## التحقق وحدوده

447 اختبار PHP / 2261 تحققًا ناجحة، منها 22 اختبارًا للـSEO تشمل الفهرسة واللغة وHost وsubdirectory والخصوصية والثبات والترحيل. نجح بناء Vite وتجميع Blade، واختبارات واجهة المراحل والمظهر الـ12.

اختُبرت SQLite الاختبارية المعزولة، ولم يُختبر MySQL أو Googlebot حي أو ملكية الدومين أو PageSpeed في الإنتاج. لا توجد ترجمة آلية لمحتوى الفرص أو نظام منتجات/تقييمات/مراجعات تجارية في هذه المرحلة.

## مراجع رسمية

https://developers.google.com/search/docs/crawling-indexing/sitemaps/build-sitemap
https://developers.google.com/search/docs/specialty/international/localized-versions
https://developers.google.com/search/docs/crawling-indexing/robots/intro
https://developers.google.com/search/docs/fundamentals/seo-starter-guide
https://developers.google.com/search/help/crawling-index-faq
