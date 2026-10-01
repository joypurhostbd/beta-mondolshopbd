<?php

namespace App\Services\Support;

class BangladeshGeoDictionary
{
    /**
     * Complete dictionary of 64 districts in Bangladesh with aliases and division.
     *
     * @return array<string, array{aliases: array<string>, division: string}>
     */
    public static function getDistricts(): array
    {
        return [
            'Dhaka' => ['aliases' => ['dhaka', 'dhk', 'ঢাকা', 'dhakah', 'dokha'], 'division' => 'Dhaka'],
            'Gazipur' => ['aliases' => ['gazipur', 'gajipur', 'গাজীপুর', 'টঙ্গী', 'tongi', 'কালিয়াকৈর', 'kaliakair', 'কাপাসিয়া', 'kapasia', 'শ্রীপুর', 'sreepur'], 'division' => 'Dhaka'],
            'Narayanganj' => ['aliases' => ['narayanganj', 'narayangonj', 'নারায়ণগঞ্জ', 'নারায়নগঞ্জ', 'সোনারগাঁও', 'sonargaon', 'রূপগঞ্জ', 'rupganj', 'আড়াইহাজার', 'araihazar'], 'division' => 'Dhaka'],
            'Narsingdi' => ['aliases' => ['narsingdi', 'norshingdi', 'নরসিংদী', 'মাধবদী', 'madhabdi', 'পলাশ', 'palash', 'শিবপুর', 'shibpur', 'রায়পুরা', 'raipura'], 'division' => 'Dhaka'],
            'Tangail' => ['aliases' => ['tangail', 'টাঙ্গাইল', 'মির্জাপুর', 'mirzapur', 'ঘাটাইল', 'ghatail', 'সখিপুর', 'sakhipur', 'মধুপুর', 'madhupur', 'কালিহাতী', 'kalihati', 'বাসাইল', 'basail'], 'division' => 'Dhaka'],
            'Faridpur' => ['aliases' => ['faridpur', 'ফরিদপুর', 'ভাঙ্গা', 'bhanga', 'বোয়ালমারী', 'boalmari', 'মধুখালী', 'madhukhali'], 'division' => 'Dhaka'],
            'Gopalganj' => ['aliases' => ['gopalganj', 'gopalgonj', 'গোপালগঞ্জ', 'টুঙ্গিপাড়া', 'tungipara', 'মুকসুদপুর', 'muksudpur'], 'division' => 'Dhaka'],
            'Madaripur' => ['aliases' => ['madaripur', 'মাদারীপুর', 'মাদারিপুর', 'শিবচর', 'shibchar', 'কালকিনি', 'kalkini'], 'division' => 'Dhaka'],
            'Manikganj' => ['aliases' => ['manikganj', 'manikgonj', 'মানিকগঞ্জ', 'সিংগাইর', 'singair'], 'division' => 'Dhaka'],
            'Munshiganj' => ['aliases' => ['munshiganj', 'munshigonj', 'মুন্সীগঞ্জ', 'মুন্সিগঞ্জ', 'শ্রীনগর', 'sreenagar', 'গজারিয়া', 'gojaria', 'সিরাজদিখান', 'sirajdikhan'], 'division' => 'Dhaka'],
            'Rajbari' => ['aliases' => ['rajbari', 'রাজবাড়ী', 'রাজবাড়ি', 'পাংশা', 'pangsha'], 'division' => 'Dhaka'],
            'Shariatpur' => ['aliases' => ['shariatpur', 'শরীয়তপুর', 'শরীয়তপুর', 'নড়িয়া', 'naria', 'জাজিরা', 'zajira', 'ভেদরগঞ্জ', 'bhedarganj'], 'division' => 'Dhaka'],
            'Kishoreganj' => ['aliases' => ['kishoreganj', 'kishoregonj', 'কিশোরগঞ্জ', 'ভৈরব', 'bhairab', 'বাজিতপুর', 'bajitpur'], 'division' => 'Dhaka'],

            'Chattogram' => ['aliases' => ['chattogram', 'chittagong', 'ctg', 'চট্টগ্রাম', 'চট্রগ্রাম', 'পটিয়া', 'patia', 'potiya', 'সীতাকুণ্ড', 'sitakunda', 'মীরসরাই', 'mirsharai', 'হাটহাজারী', 'hathazari', 'বোয়ালখালী', 'boalkhali', 'রাঙ্গুনিয়া', 'rangunia', 'সন্দ্বীপ', 'sandwip', 'বাঁশখালী', 'banskhali', 'আনোয়ারা', 'anwara'], 'division' => 'Chattogram'],
            'Cox\'s Bazar' => ['aliases' => ['cox\'s bazar', 'coxs bazar', 'coxsbazar', 'কক্সবাজার', 'কক্স বাজার', 'চকরিয়া', 'chakoria', 'চকোরিয়া', 'টেকনাফ', 'teknaf', 'উখিয়া', 'ukhia', 'মহেশখালী', 'moheshkhali', 'পেকুয়া', 'pekua', 'রামু', 'ramu'], 'division' => 'Chattogram'],
            'Cumilla' => ['aliases' => ['cumilla', 'comilla', 'কুমিল্লা', 'কুমিল্লাহ', 'দাউদকান্দি', 'daudkandi', 'দেবীদ্বার', 'debidwar', 'চান্দিনা', 'chandina', 'লাকসাম', 'laksham', 'মুরাদনগর', 'muradnagar', 'চৌদ্দগ্রাম', 'chouddagram', 'হোমনা', 'homna', 'বরুড়া', 'barura'], 'division' => 'Chattogram'],
            'Brahmanbaria' => ['aliases' => ['brahmanbaria', 'brammon baria', 'b.baria', 'ব্রাহ্মণবাড়িয়া', 'ব্রাহ্মণবাড়িয়া', 'বি-বাড়িয়া', 'বিবাড়িয়া', 'কসবা', 'kasba', 'নবীনগর', 'nabinagar', 'আশুগঞ্জ', 'ashuganj', 'সরাইল', 'sarail', 'বাঞ্ছারামপুর', 'bancharampur'], 'division' => 'Chattogram'],
            'Chandpur' => ['aliases' => ['chandpur', 'চাঁদপুর', 'চাদপুর', 'হাজীগঞ্জ', 'hajiganj', 'শাহরাস্তি', 'shahrasti', 'মতলব', 'matlab', 'কচুয়া', 'kachua'], 'division' => 'Chattogram'],
            'Feni' => ['aliases' => ['feni', 'ফেনী', 'ফেনি', 'দাগনভূঁইয়া', 'daganbhuiyan', 'পরশুরাম', 'parshuram', 'ছাগলনাইয়া', 'chhagalnaiya'], 'division' => 'Chattogram'],
            'Lakshmipur' => ['aliases' => ['lakshmipur', 'laksmipur', 'laxmipur', 'লক্ষ্মীপুর', 'লক্ষীপুর', 'রামগঞ্জ', 'ramganj', 'রায়পুর', 'raipur', 'রামগতি', 'ramgati'], 'division' => 'Chattogram'],
            'Noakhali' => ['aliases' => ['noakhali', 'নোয়াখালী', 'নোয়াখালী', 'চাটখিল', 'chatkhil', 'বেগমগঞ্জ', 'begumganj', 'সেনবাগ', 'senbagh', 'কোম্পানীগঞ্জ', 'companyganj', 'সুবর্ণচর', 'subarnachar', 'হাতিয়া', 'hatia'], 'division' => 'Chattogram'],
            'Bandarban' => ['aliases' => ['bandarban', 'বান্দরবান', 'রোয়াংছড়ি', 'rowangchhari', 'রুমা', 'ruma', 'থানচি', 'thanchi', 'লামা', 'lama'], 'division' => 'Chattogram'],
            'Khagrachhari' => ['aliases' => ['khagrachhari', 'khagrachari', 'খাগড়াছড়ি', 'খাগড়াছড়ি', 'মাটিরাঙ্গা', 'matiranga', 'দীঘিনালা', 'dighinala', 'গুইমারা', 'guimara'], 'division' => 'Chattogram'],
            'Rangamati' => ['aliases' => ['rangamati', 'রাঙ্গামাটি', 'রাঙামাটি', 'বাঘাইছড়ি', 'baghaichhari', 'কাপ্তাই', 'kaptai'], 'division' => 'Chattogram'],

            'Rajshahi' => ['aliases' => ['rajshahi', 'রাজশাহী', 'বাঘা', 'bagha', 'পুঠিয়া', 'puthia', 'গোদাগাড়ী', 'godagari', 'তানোর', 'tanore', 'চারঘাট', 'charghat', 'বাগমারা', 'bagmara'], 'division' => 'Rajshahi'],
            'Bogura' => ['aliases' => ['bogura', 'bogra', 'বগুড়া', 'বগুড়া', 'শেরপুর', 'sherpur, bogra', 'শিবগঞ্জ', 'shibganj', 'গাবতলী', 'gabtali', 'দুপচাঁচিয়া', 'dupchanchia', 'কাহালু', 'kahaloo', 'নন্দীগ্রাম', 'nandigram', 'ধুনট', 'dhunat', 'সারিয়াকান্দি', 'sariakandi', 'আদমদীঘি', 'adamdighi', 'shajahanpur', 'শাজাহানপুর', 'গাতনশহর', 'gatonshahar'], 'division' => 'Rajshahi'],
            'Joypurhat' => ['aliases' => ['joypurhat', 'জয়পুরহাট', 'জয়পুরহাট', 'পাঁচবিবি', 'panchbibi', 'কালাই', 'kalai', 'ক্ষেতলাল', 'khetlal', 'আক্কেলপুর', 'akkelpur'], 'division' => 'Rajshahi'],
            'Naogaon' => ['aliases' => ['naogaon', 'নওগাঁ', 'নওগা', 'পত্নীতলা', 'patnitala', 'মহাদেবপুর', 'mohadevpur', 'মান্দা', 'manda', 'ধামইরহাট', 'dhamoirhat'], 'division' => 'Rajshahi'],
            'Natore' => ['aliases' => ['natore', 'নাটোর', 'বড়াইগ্রাম', 'baraigram', 'সিংড়া', 'singra', 'লালপুর', 'lalpur', 'গুরুদাসপুর', 'gurudaspur', 'বাগাতিপাড়া', 'bagatipara'], 'division' => 'Rajshahi'],
            'Chapai Nawabganj' => ['aliases' => ['chapai nawabganj', 'chapainawabganj', 'chapai', 'nawabganj', 'চাঁপাইনবাবগঞ্জ', 'চাপাইনবাবগঞ্জ', 'গোমস্তাপুর', 'gomostapur', 'নাচোল', 'nachole'], 'division' => 'Rajshahi'],
            'Pabna' => ['aliases' => ['pabna', 'পাবনা', 'ঈশ্বরদী', 'ishwardi', 'বেড়া', 'bera', 'সাঁথিয়া', 'santhia', 'সুজানগর', 'sujanagar', 'চাটমোহর', 'chatmohar'], 'division' => 'Rajshahi'],
            'Sirajganj' => ['aliases' => ['sirajganj', 'sirajgonj', 'সিরাজগঞ্জ', 'শাহজাদপুর', 'shahjadpur', 'উল্লাপাড়া', 'ullahpara', 'বেলকুচি', 'belkuchi', 'কাজীপুর', 'kazipur', 'রায়গঞ্জ', 'raiganj'], 'division' => 'Rajshahi'],

            'Khulna' => ['aliases' => ['khulna', 'খুলনা', 'খালিশপুর', 'khalishpur', 'দৌলতপুর', 'daulatpur', 'বটিয়াঘাটা', 'batiaghata', 'ডুমুরিয়া', 'dumuria', 'রূপসা', 'rupsha', 'তেরখাদা', 'terokhada', 'পাইকগাছা', 'paikgachha', 'ফুলতলা', 'phultala', 'কয়রা', 'koyra'], 'division' => 'Khulna'],
            'Bagerhat' => ['aliases' => ['bagerhat', 'বাগেরহাট', 'মোংলা', 'mongla', 'মোড়েলগঞ্জ', 'morelganj', 'শরণখোলা', 'sharankhola'], 'division' => 'Khulna'],
            'Chuadanga' => ['aliases' => ['chuadanga', 'চুয়াডাঙ্গা', 'চুয়াডাঙ্গা', 'আলমডাঙ্গা', 'alamdanga', 'দামুড়হুদা', 'damurhuda', 'জীবননগর', 'jibannagar'], 'division' => 'Khulna'],
            'Jashore' => ['aliases' => ['jashore', 'jessore', 'যশোর', 'নওয়াপাড়া', 'noapara', 'বেনাপোল', 'benapole', 'ঝিকরগাছা', 'jhikargachha', 'কেশবপুর', 'keshabpur', 'বাঘারপাড়া', 'bagherpara', 'শার্শা', 'sharsha'], 'division' => 'Khulna'],
            'Jhenaidah' => ['aliases' => ['jhenaidah', 'jhenaidha', 'ঝিনাইদহ', 'কালীগঞ্জ', 'kaliganj', 'কোটচাঁদপুর', 'kotchandpur', 'শৈলকূপা', 'shailakupa', 'মহেশপুর', 'moheshpur'], 'division' => 'Khulna'],
            'Kushtia' => ['aliases' => ['kushtia', 'কুষ্টিয়া', 'কুষ্টিয়া', 'ভেড়ামারা', 'bheramara', 'কুমারখালী', 'kumarkhali', 'মিরপুর', 'mirpur, kushtia', 'খোকসা', 'khoksa'], 'division' => 'Khulna'],
            'Magura' => ['aliases' => ['magura', 'মাগুরা', 'শ্রীপুর', 'মহম্মদপুর', 'শালিখা', 'shalikha'], 'division' => 'Khulna'],
            'Meherpur' => ['aliases' => ['meherpur', 'মেহেরপুর', 'গাংনী', 'gangni', 'মুজিবনগর', 'mujibnagar'], 'division' => 'Khulna'],
            'Narail' => ['aliases' => ['narail', 'নড়াইল', 'নড়াইল', 'লোহাগড়া', 'lohagara', 'কালিয়া', 'kalia'], 'division' => 'Khulna'],
            'Satkhira' => ['aliases' => ['satkhira', 'সাতক্ষীরা', 'কলারোয়া', 'kalaroa', 'তালা', 'tala', 'শ্যামনগর', 'shyamnagar', 'আশাশুনি', 'ashashuni'], 'division' => 'Khulna'],

            'Barishal' => ['aliases' => ['barishal', 'barisal', 'বরিশাল', 'গৌরনদী', 'gournadi', 'বাকেরগঞ্জ', 'bakerganj', 'বাবুগঞ্জ', 'babuganj', 'উজিরপুর', 'wazirpur', 'বানারীপাড়া', 'banaripara', 'মুলাদী', 'muladi'], 'division' => 'Barishal'],
            'Barguna' => ['aliases' => ['barguna', 'বরগুনা', 'আমতলী', 'amtali', 'পাথরঘাটা', 'patharghata', 'বেতাগী', 'betagi'], 'division' => 'Barishal'],
            'Bhola' => ['aliases' => ['bhola', 'ভোলা', 'বোরহানউদ্দিন', 'borhanuddin', 'চরফ্যাশন', 'charfasson', 'দৌলতখান', 'daulatkhan', 'লালমোহন', 'lalmohan', 'তজুমদ্দিন', 'tazumuddin'], 'division' => 'Barishal'],
            'Jhalokati' => ['aliases' => ['jhalokati', 'jhalokathi', 'ঝালকাঠি', 'ঝালকাঠী', 'নলছিটি', 'nalchity', 'রাজাপুর', 'rajapur'], 'division' => 'Barishal'],
            'Patuakhali' => ['aliases' => ['patuakhali', 'পটুয়াখালী', 'পটুয়াখালী', 'কলাপাড়া', 'kalapara', 'কুয়াকাটা', 'kuakata', 'গলাচিপা', 'galachipa', 'বাউফল', 'bauphal', 'মির্জাগঞ্জ', 'mirzaganj'], 'division' => 'Barishal'],
            'Pirojpur' => ['aliases' => ['pirojpur', 'perojpur', 'পিরোজপুর', 'ভান্ডারিয়া', 'bhandaria', 'মঠবাড়িয়া', 'mathbaria', 'নাজিরপুর', 'nazirpur', 'স্বরূপকাঠি', 'swarupkathi', 'নেছারাবাদ', 'nesarabad'], 'division' => 'Barishal'],

            'Sylhet' => ['aliases' => ['sylhet', 'সিলেট', 'গোলাপগঞ্জ', 'golapganj', 'বিয়ানীবাজার', 'beanibazar', 'দক্ষিণ সুরমা', 'south surma', 'বিশ্বনাথ', 'biswanath', 'জৈন্তাপুর', 'jaintiapur', 'বালাগঞ্জ', 'balaganj', 'কানাইঘাট', 'kanaighat', 'ফেঞ্চুগঞ্জ', 'fenchuganj', 'জাকারিয়া', 'zakiganj'], 'division' => 'Sylhet'],
            'Habiganj' => ['aliases' => ['habiganj', 'hobiganj', 'হবিগঞ্জ', 'নবীগঞ্জ', 'nabiganj', 'মাধবপুর', 'madhabpur', 'চুনারুঘাট', 'chunarughat', 'বাহুবল', 'bahubal', 'আজমিরীগঞ্জ', 'ajmiriganj'], 'division' => 'Sylhet'],
            'Moulvibazar' => ['aliases' => ['moulvibazar', 'moulvibajar', 'maulvibazar', 'মৌলভীবাজার', 'শ্রীমঙ্গল', 'sreemangal', 'কুলাউড়া', 'kulaura', 'বড়লেখা', 'barlekha', 'কমলগঞ্জ', 'kamalganj', 'জুড়ী', 'juri'], 'division' => 'Sylhet'],
            'Sunamganj' => ['aliases' => ['sunamganj', 'sunamgonj', 'সুনামগঞ্জ', 'ছাতক', 'chhatak', 'জগন্নাথপুর', 'jagannathpur', 'দিরাই', 'dirai', 'তাহিরপুর', 'tahirpur'], 'division' => 'Sylhet'],

            'Rangpur' => ['aliases' => ['rangpur', 'রংপুর', 'বদরগঞ্জ', 'badarganj', 'পীরগঞ্জ', 'pirganj', 'পীরগাছা', 'pirgachha', 'মিঠাপুকুর', 'mithapukur', 'তারাগঞ্জ', 'taraganj', 'কাউনিয়া', 'kaunia'], 'division' => 'Rangpur'],
            'Dinajpur' => ['aliases' => ['dinajpur', 'দিনাজপুর', 'বীরগঞ্জ', 'birganj', 'ফুলবাড়ী', 'fulbari, dinajpur', 'বিরামপুর', 'birampur', 'পার্বতীপুর', 'parbatipur', 'নবাবগঞ্জ', 'ঘোড়াঘাট', 'ghoraghat', 'খানসামা', 'khansama'], 'division' => 'Rangpur'],
            'Gaibandha' => ['aliases' => ['gaibandha', 'গাইবান্ধা', 'গোবিন্দগঞ্জ', 'gobindaganj', 'পলাশবাড়ী', 'palashbari', 'সুন্দরগঞ্জ', 'sundarganj', 'সাদুল্লাপুর', 'sadullapur'], 'division' => 'Rangpur'],
            'Kurigram' => ['aliases' => ['kurigram', 'কুড়িগ্রাম', 'কুড়িগ্রাম', 'ভুরুঙ্গামারী', 'bhurungamari', 'নাগেশ্বরী', 'nageshwari', 'রৌমারী', 'rowmari', 'উলিপুর', 'ulipur', 'ফুলবাড়ী', 'fulbari'], 'division' => 'Rangpur'],
            'Lalmonirhat' => ['aliases' => ['lalmonirhat', 'লালমনিরহাট', 'পাটগ্রাম', 'patgram', 'হাতীবান্ধা', 'hatibandha'], 'division' => 'Rangpur'],
            'Nilphamari' => ['aliases' => ['nilphamari', 'নীলফামারী', 'নিলফামারী', 'সৈয়দপুর', 'saidpur', 'ডোমার', 'domar', 'জলঢাকা', 'jaldhaka', 'ডিমলা', 'dimla'], 'division' => 'Rangpur'],
            'Panchagarh' => ['aliases' => ['panchagarh', 'পঞ্চগড়', 'পঞ্চগড়', 'তেঁতুলিয়া', 'tetulia', 'বোদা', 'boda', 'দেবীগঞ্জ', 'debiganj'], 'division' => 'Rangpur'],
            'Thakurgaon' => ['aliases' => ['thakurgaon', 'ঠাকুরগাঁও', 'ঠাকুরগা', 'বালিয়াডাঙ্গী', 'baliadangi', 'রানীশংকৈল', 'ranisankail'], 'division' => 'Rangpur'],

            'Mymensingh' => ['aliases' => ['mymensingh', 'ময়মনসিংহ', 'ময়মনসিংহ', 'ত্রিশাল', 'trishal', 'ভালুকা', 'bhaluka', 'মুক্তাগাছা', 'muktagachha', 'গফরগাঁও', 'gafargaon', 'ফুলপুর', 'phulpur', 'হালুয়াঘাট', 'haluaghat', 'ধোবাউড়া', 'dhobaura', 'ঈশ্বরগঞ্জ', 'ishwarganj'], 'division' => 'Mymensingh'],
            'Jamalpur' => ['aliases' => ['jamalpur', 'জামালপুর', 'সরিষাবাড়ী', 'sarishabari', 'ইসলামপুর', 'islampur', 'মেলান্দহ', 'melandaha', 'মাদারগঞ্জ', 'madarganj', 'বকশীগঞ্জ', 'bakshiganj', 'দেওয়ানগঞ্জ', 'dewanganj'], 'division' => 'Mymensingh'],
            'Netrokona' => ['aliases' => ['netrokona', 'নেত্রকোণা', 'নেত্রকোনা', 'কেন্দুয়া', 'kendua', 'মোহনগঞ্জ', 'mohangonj', 'দূর্গাপুর', 'durgapur', 'পূর্বধলা', 'purbadhala'], 'division' => 'Mymensingh'],
            'Sherpur' => ['aliases' => ['sherpur', 'শেরপুর', 'নকলা', 'nakla', 'নালিতাবাড়ী', 'nalitabari', 'ঝিনাইগাতী', 'jhinaigati', 'শ্রীবরদী', 'sreebardi'], 'division' => 'Mymensingh'],
        ];
    }

    /**
     * Common Dhaka City Thanas and Neighborhoods.
     *
     * @return array<string, array<string>>
     */
    public static function getDhakaThanas(): array
    {
        return [
            'Mirpur' => ['mirpur', 'মিরপুর', 'pallabi', 'পল্লবী', 'shewrapara', 'শেওড়াপাড়া', 'kazipara', 'কাজীপাড়া', 'paikpara'],
            'Uttara' => ['uttara', 'উত্তরা', 'uttarkhan', 'উত্তরখান', 'dakshinkhan', 'দক্ষিণখান', 'turag', 'তুরাগ', 'diabari', 'দিয়াবাড়ী'],
            'Dhanmondi' => ['dhanmondi', 'ধানমন্ডি', 'kalabagan', 'কলাবাগান', 'sobanbag', 'sobhanbag', 'sukrabad', 'শুক্রাবাদ'],
            'Gulshan' => ['gulshan', 'গুলশান', 'niketan', 'নিকেতন'],
            'Banani' => ['banani', 'বনানী'],
            'Mohammadpur' => ['mohammadpur', 'মোহাম্মদপুর', 'adabor', 'আদাবর', 'bosila', 'বসিলা', 'kallyanpur', 'কল্যাণপুর', 'shyamoli', 'শ্যামলী'],
            'Badda' => ['badda', 'বাড্ডা', 'aftabnagar', 'আফতাবনগর', 'shahjadpur', 'শাহজাদপুর'],
            'Bashundhara R/A' => ['bashundhara', 'বসুন্ধরা'],
            'Khilkhet' => ['khilkhet', 'খিলক্ষেত', 'nikunja', 'নিকুঞ্জ'],
            'Motijheel' => ['motijheel', 'মতিঝিল', 'dilkusha', 'দিলকুশা'],
            'Paltan' => ['paltan', 'পল্টন', 'press club'],
            'Jatrabari' => ['jatrabari', 'যাত্রাবাড়ী', 'যাত্রাবাড়ি', 'dhalpur', 'ধলপুর'],
            'Rampura' => ['rampura', 'রামপুরা', 'banasree', 'বনশ্রী'],
            'Khilgaon' => ['khilgaon', 'খিলগাঁও', 'goran', 'গোড়ান', 'taltola', 'তালতলা'],
            'Old Dhaka' => ['old dhaka', 'পুরান ঢাকা', 'lalbagh', 'লালবাগ', 'sadarghat', 'সদরঘাট', 'chawkbazar', 'চকবাজার', 'sutrapur', 'সূত্রাপুর', 'kotwali', 'কোতোয়ালী', 'wari', 'ওয়ারী'],
            'Savar' => ['savar', 'সাভার', 'ashulia', 'আশুলিয়া', 'epz'],
            'Keraniganj' => ['keraniganj', 'কেরানীগঞ্জ', 'কেরানিগঞ্জ'],
            'Dhamrai' => ['dhamrai', 'ধামরাই'],
            'Mohakhali' => ['mohakhali', 'মহাখালী', 'wireless'],
            'Farmgate' => ['farmgate', 'ফার্মগেট', 'tejgaon', 'তেজগাঁও', 'nakhalpara'],
            'Shantinagar' => ['shantinagar', 'শান্তিনগর', 'malibagh', 'মালিবাগ', 'mouchak', 'মৌচাক', 'kakrail', 'কাকরাইল'],
            'Mugda' => ['mugda', 'মুগদা', 'manda', 'মান্দা'],
        ];
    }
}
