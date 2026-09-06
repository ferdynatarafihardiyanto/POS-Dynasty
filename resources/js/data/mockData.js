export const CATEGORIES = [
    { id: 'all', nama: 'Semua Menu', icon: '🍽️' },
    { id: 'popular', nama: 'Menu Populer', icon: '🔥' },
    { id: 'makanan-utama', nama: 'Makanan Utama', icon: '🍱' },
    { id: 'mie-bakso', nama: 'Mie & Bakso', icon: '🍜' },
    { id: 'minuman', nama: 'Minuman', icon: '☕' },
    { id: 'snack-dessert', nama: 'Snack & Dessert', icon: '🍟' },
];

export const PROMO_ITEMS = [
    {
        id: 101,
        backend_id: 1,
        nama: 'Sop Buntut Bakar Madu',
        kategori_id: 'makanan-utama',
        harga: 68000,
        harga_coret: 85000,
        diskon: 'DISKON 20%',
        badge: 'Rekomendasi Koki',
        is_promo: true,
        rating: 4.9,
        reviews_count: 128,
        prep_time: '15 - 20 Menit',
        portion_tag: 'Porsi Hangat',
        gambar: 'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=800&q=80',
        deskripsi_singkat: 'Buntut sapi bakar saus madu gurih manis empuk disajikan kuah kaldu.',
        deskripsi: 'Buntut sapi pilihan dibakar empuk dengan karamelisasi saus madu khas Nusantara, disajikan bersama semangkuk kuah kaldu sapi bening rempah yang gurih hangat, emping melinjo renyah, sambal ijo segar, dan perasan jeruk limau.',
        options_carbs: {
            title: 'Pilihan Nasi / Karbohidrat',
            required: true,
            subtitle: 'Pilih 1 jenis pendamping karbohidrat utama',
            choices: [
                { id: 'nasi_putih', name: 'Nasi Putih Pulen', desc: 'Beras Cianjur hangat pulen', price: 6000, is_default: true },
                { id: 'nasi_uduk', name: 'Nasi Uduk Gurih', desc: 'Santan kelapa murni & rempah serai', price: 8000 },
                { id: 'tanpa_nasi', name: 'Tanpa Nasi', desc: 'Hanya sop buntut bakar komplit', price: 0 }
            ]
        },
        options_spice: {
            title: 'Tingkat Kepedasan Sambal',
            required: true,
            subtitle: 'Pilih 1 level pedas sambal',
            choices: [
                { id: 'tidak_pedas', name: 'Tidak Pedas / Sambal Terpisah', tag: 'Aman', tagColor: 'bg-emerald-100 text-emerald-700', desc: 'Sambal disajikan di mangkok kecil terpisah', price: 0, is_default: true },
                { id: 'sedang', name: 'Sedang', tag: 'Sedang', tagColor: 'bg-amber-100 text-amber-700', desc: 'Olesan cabai rawit hijau pas gurih', price: 0 },
                { id: 'pedas_nampol', name: 'Pedas Nampol', tag: 'Pedas 🌶️🌶️🌶️', tagColor: 'bg-red-100 text-red-700 font-bold', desc: 'Ekstra lumuran ulekan cabai rawit merah membara', price: 0 }
            ]
        },
        options_toppings: {
            title: 'Tambahan Topping / Ekstra',
            multi: true,
            subtitle: 'Bisa Multi-pilih',
            choices: [
                { id: 'top_emping', name: 'Ekstra Emping Melinjo', desc: '1 keranjang kecil renyah & gurih', price: 5000 },
                { id: 'top_telur', name: 'Telur Mata Sapi', desc: 'Kuning telur setengah matang/matang', price: 6000 },
                { id: 'top_kuah', name: 'Ekstra Kuah Kaldu Sup', desc: '1 mangkok kuah hangat kaldu rempah', price: 8000 },
                { id: 'top_esteh', name: 'Es Teh Manis Jumbo', desc: 'Teh seduh melati segar dingin', price: 7000 }
            ]
        }
    },
    {
        id: 102,
        backend_id: 2,
        nama: 'Paket Ayam Taliwang',
        kategori_id: 'makanan-utama',
        harga: 48000,
        harga_coret: 55000,
        diskon: 'DISKON 15%',
        badge: 'CHEF CHOICE',
        is_promo: true,
        rating: 4.8,
        reviews_count: 94,
        prep_time: '15 Menit',
        portion_tag: 'Pedas Mantap',
        gambar: 'https://images.unsplash.com/photo-1598515214211-89d3c73ae83b?auto=format&fit=crop&w=800&q=80',
        deskripsi_singkat: 'Ayam bakar bumbu pedas manis khas Lombok disajikan lalapan segar.',
        deskripsi: 'Ayam muda bakar empuk dibumbui khas Taliwang dengan perpaduan terasi, cabai rawit, dan jeruk limau yang meresap sempurna hingga ke tulang.',
        options_carbs: {
            title: 'Pilihan Nasi / Karbohidrat',
            required: true,
            subtitle: 'Pilih 1 jenis pendamping karbohidrat utama',
            choices: [
                { id: 'nasi_putih', name: 'Nasi Putih Pulen', desc: 'Beras Cianjur hangat pulen', price: 6000, is_default: true },
                { id: 'nasi_uduk', name: 'Nasi Uduk Gurih', desc: 'Santan kelapa murni & rempah serai', price: 8000 },
                { id: 'tanpa_nasi', name: 'Tanpa Nasi', desc: 'Hanya ayam taliwang bakar', price: 0 }
            ]
        },
        options_spice: {
            title: 'Tingkat Kepedasan Sambal',
            required: true,
            subtitle: 'Pilih 1 level pedas sambal',
            choices: [
                { id: 'sedang', name: 'Sedang', tag: 'Sedang', tagColor: 'bg-amber-100 text-amber-700', desc: 'Pedas wajar bumbu taliwang', price: 0, is_default: true },
                { id: 'extra_pedas', name: 'Super Pedas', tag: 'Pedas 🔥🔥', tagColor: 'bg-red-100 text-red-700', desc: 'Ekstra cabai rawit merah pedas nendang', price: 0 }
            ]
        },
        options_toppings: {
            title: 'Tambahan Topping / Ekstra',
            multi: true,
            subtitle: 'Bisa Multi-pilih',
            choices: [
                { id: 'top_plecing', name: 'Ekstra Plecing Kangkung', desc: 'Kangkung segar sambal tomat terasi', price: 8000 },
                { id: 'top_tahu_tempe', name: 'Tahu & Tempe Goreng', desc: '2 pcs tahu dan 2 pcs tempe gurih', price: 6000 },
                { id: 'top_esteh', name: 'Es Teh Manis Jumbo', desc: 'Teh seduh melati segar dingin', price: 7000 }
            ]
        }
    }
];

export const ALL_MENU_ITEMS = [
    PROMO_ITEMS[0],
    PROMO_ITEMS[1],
    {
        id: 103,
        backend_id: 3,
        nama: 'Nasi Goreng Spesial',
        kategori_id: 'makanan-utama',
        harga: 35000,
        badge: 'Populer',
        rating: 4.9,
        reviews_count: 210,
        prep_time: '10 - 15 Menit',
        portion_tag: 'Porsi Jumbo',
        gambar: 'https://images.unsplash.com/photo-1603133872878-684f208fb84b?auto=format&fit=crop&w=800&q=80',
        deskripsi_singkat: 'Nasi goreng istimewa harum butter dengan ayam suwir, sosis, udang dan telur.',
        deskripsi: 'Nasi goreng racikan resep turun-temurun dengan aroma wok-hei kuat, suwiran ayam gurih, udang segar, telur mata sapi, acar segar dan kerupuk udang renyah.',
        options_spice: {
            title: 'Tingkat Kepedasan',
            required: true,
            subtitle: 'Pilih 1 level pedas',
            choices: [
                { id: 'tidak_pedas', name: 'Tidak Pedas', tag: 'Anak-anak', tagColor: 'bg-emerald-100 text-emerald-700', desc: 'Tanpa cabai sama sekali', price: 0, is_default: true },
                { id: 'sedang', name: 'Sedang (Cabe 3)', tag: 'Sedang', tagColor: 'bg-amber-100 text-amber-700', desc: 'Pas untuk santai', price: 0 },
                { id: 'pedas_gila', name: 'Pedas Gila (Cabe 10)', tag: 'Pedas 🔥🔥', tagColor: 'bg-red-100 text-red-700', desc: 'Ulekan rawit merah membakar lidah', price: 0 }
            ]
        },
        options_toppings: {
            title: 'Tambahan Topping',
            multi: true,
            subtitle: 'Bisa Multi-pilih',
            choices: [
                { id: 'top_telur_dadar', name: 'Ekstra Telur Dadar Krispi', desc: 'Garing renyah keemasan', price: 6000 },
                { id: 'top_sate', name: 'Sate Ayam (3 tusuk)', desc: 'Dengan bumbu kacang gurih', price: 12000 },
                { id: 'top_esteh', name: 'Es Teh Manis', desc: 'Segar manis pas', price: 6000 }
            ]
        }
    },
    {
        id: 104,
        backend_id: 4,
        nama: 'Ayam Bakar Madu',
        kategori_id: 'makanan-utama',
        harga: 38000,
        badge: 'Best Seller',
        rating: 4.8,
        reviews_count: 156,
        prep_time: '15 Menit',
        portion_tag: 'Porsi Hangat',
        gambar: 'https://images.unsplash.com/photo-1626082927389-6cd097cdc6ec?auto=format&fit=crop&w=800&q=80',
        deskripsi_singkat: '1/4 ekor ayam empuk dengan olesan madu legit bakar arang.',
        deskripsi: 'Potongan paha/dada ayam pilihan yang diungkep bumbu rempah kuning lalu dipanggang dengan olesan madu hutan alami dan kecap manis premium.',
        options_carbs: {
            title: 'Pilihan Nasi',
            required: true,
            subtitle: 'Pilih pendamping karbohidrat',
            choices: [
                { id: 'nasi_putih', name: 'Nasi Putih Pulen', desc: 'Hangat dan wangi', price: 6000, is_default: true },
                { id: 'nasi_uduk', name: 'Nasi Uduk Gurih', desc: 'Gurih serai pandan', price: 8000 },
                { id: 'tanpa_nasi', name: 'Tanpa Nasi', desc: 'Hanya ayam bakar', price: 0 }
            ]
        },
        options_spice: {
            title: 'Pilihan Sambal',
            required: true,
            subtitle: 'Pilih 1 sambal favorit',
            choices: [
                { id: 'sambal_terasi', name: 'Sambal Terasi Bakar', tag: 'Favorit', tagColor: 'bg-red-100 text-red-700', desc: 'Aroma wangi terasi bakar', price: 0, is_default: true },
                { id: 'sambal_matah', name: 'Sambal Matah Bali', tag: 'Segar', tagColor: 'bg-amber-100 text-amber-700', desc: 'Irisan bawang & serai segar', price: 0 }
            ]
        },
        options_toppings: {
            title: 'Ekstra Topping',
            multi: true,
            subtitle: 'Bisa Multi-pilih',
            choices: [
                { id: 'top_lalap', name: 'Ekstra Lalapan Komplit', desc: 'Mentimun, kemangi, kol goreng', price: 4000 },
                { id: 'top_tempe', name: 'Tempe Mendoan (2 pcs)', desc: 'Hangat dengan kecap rawit', price: 7000 }
            ]
        }
    },
    {
        id: 105,
        backend_id: 5,
        nama: 'Mie Aceh Daging Sapi',
        kategori_id: 'mie-bakso',
        harga: 42000,
        badge: 'Khas Nusantara',
        rating: 4.7,
        reviews_count: 88,
        prep_time: '12 - 15 Menit',
        portion_tag: 'Kental Rempah',
        gambar: 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?auto=format&fit=crop&w=800&q=80',
        deskripsi_singkat: 'Mie tebal kuning kenyal khas Aceh dengan potongan daging sapi empuk.',
        deskripsi: 'Mie khas tanah Rencong dimasak tumis nyemek dengan racikan 16 rempah kari pekat, irisan daging sapi empuk, tauge segar, emping melinjo, dan acar bawang merah.',
        options_spice: {
            title: 'Tingkat Kepedasan & Kuah',
            required: true,
            subtitle: 'Pilih jenis penyajian',
            choices: [
                { id: 'mie_nyemek_sedang', name: 'Nyemek Sedang', tag: 'Rekomendasi', tagColor: 'bg-amber-100 text-amber-700', desc: 'Kuah kental sedikit basah gurih', price: 0, is_default: true },
                { id: 'mie_goreng_pedas', name: 'Goreng Kering Pedas', tag: 'Pedas 🔥', tagColor: 'bg-red-100 text-red-700', desc: 'Goreng tanpa kuah pedas mantap', price: 0 },
                { id: 'mie_kuah_banjir', name: 'Kuah Kari Pedas', tag: 'Kuah Panas', tagColor: 'bg-orange-100 text-orange-700', desc: 'Kuah kari melimpah kaya rempah', price: 0 }
            ]
        },
        options_toppings: {
            title: 'Ekstra Topping',
            multi: true,
            subtitle: 'Bisa Multi-pilih',
            choices: [
                { id: 'top_acar', name: 'Ekstra Acar Bawang & Emping', desc: 'Acar bawang merah renyah', price: 5000 },
                { id: 'top_telur_ceplok', name: 'Telur Mata Sapi', desc: 'Kuning setengah matang', price: 6000 }
            ]
        }
    },
    {
        id: 106,
        backend_id: 6,
        nama: 'Es Kopi Susu Aren',
        kategori_id: 'minuman',
        harga: 22000,
        badge: 'Rekomendasi',
        rating: 4.9,
        reviews_count: 340,
        prep_time: '5 Menit',
        portion_tag: 'Dingin Segar',
        gambar: 'https://images.unsplash.com/photo-1517701550927-30cf4ba1dba5?auto=format&fit=crop&w=800&q=80',
        deskripsi_singkat: 'Espresso robusta-arabica blend + fresh milk + gula aren murni.',
        deskripsi: 'Kopi susu khas Kedai Dynasty perpaduan espresso double shot biji kopi pilihan nusantara dengan susu segar creamy dan lelehan gula aren organik alami.',
        options_spice: {
            title: 'Level Manis (Sugar Level)',
            required: true,
            subtitle: 'Pilih takaran gula aren',
            choices: [
                { id: 'sugar_normal', name: 'Normal Sweet (100%)', tag: 'Standar', tagColor: 'bg-stone-100 text-stone-700', desc: 'Rasa otentik manis pas', price: 0, is_default: true },
                { id: 'sugar_less', name: 'Less Sweet (50%)', tag: 'Less Sweet', tagColor: 'bg-blue-100 text-blue-700', desc: 'Dominan rasa kopi yang kuat', price: 0 },
                { id: 'sugar_none', name: 'No Sugar (0%)', tag: 'Kopi Murni', tagColor: 'bg-stone-100 text-stone-700', desc: 'Tanpa gula aren', price: 0 }
            ]
        },
        options_toppings: {
            title: 'Pilihan Es & Ekstra',
            multi: true,
            subtitle: 'Kustomisasi minumanmu',
            choices: [
                { id: 'top_grass_jelly', name: 'Cincau Hitam Organik', desc: 'Lembut kenyal segar', price: 4000 },
                { id: 'top_extra_shot', name: 'Ekstra Espresso Shot', desc: 'Double caffeine kick', price: 6000 }
            ]
        }
    },
    {
        id: 107,
        backend_id: 7,
        nama: 'Sate Ayam Madura (10 tusuk)',
        kategori_id: 'makanan-utama',
        harga: 38000,
        badge: 'Populer',
        rating: 4.8,
        reviews_count: 142,
        prep_time: '15 Menit',
        portion_tag: 'Porsi Hangat',
        gambar: 'https://images.unsplash.com/photo-1555939594-58d7cb561ad1?auto=format&fit=crop&w=800&q=80',
        deskripsi_singkat: 'Daging ayam juicy panggang arang dengan saus kacang legit kental.',
        deskripsi: '10 tusuk sate daging ayam pilihan tanpa kulit dipanggang di atas arang batok kelapa, disiram bumbu kacang legit gurih, kecap manis, irisan bawang merah dan cabai rawit.',
        options_carbs: {
            title: 'Pilihan Pendamping',
            required: true,
            subtitle: 'Pilih 1 karbohidrat',
            choices: [
                { id: 'lontong', name: 'Lontong Daun Pisang', desc: 'Kenyal pulen alami', price: 6000, is_default: true },
                { id: 'nasi_putih', name: 'Nasi Putih Pulen', desc: 'Beras cianjur hangat', price: 6000 },
                { id: 'tanpa_karbo', name: 'Tanpa Nasi/Lontong', desc: 'Hanya sate 10 tusuk', price: 0 }
            ]
        },
        options_toppings: {
            title: 'Ekstra Bumbu',
            multi: true,
            subtitle: 'Bisa Multi-pilih',
            choices: [
                { id: 'top_bumbu_kacang', name: 'Ekstra Bumbu Kacang', desc: '1 mangkok saus kacang', price: 5000 },
                { id: 'top_esteh', name: 'Es Teh Manis', desc: 'Pelepas dahaga', price: 6000 }
            ]
        }
    },
    {
        id: 108,
        backend_id: 8,
        nama: 'Pisang Crispy Keju Aren',
        kategori_id: 'snack-dessert',
        harga: 25000,
        badge: 'Snack Favorit',
        rating: 4.7,
        reviews_count: 98,
        prep_time: '10 Menit',
        portion_tag: 'Garing Renyah',
        gambar: 'https://images.unsplash.com/photo-1528975604071-b4dc52a2d18c?auto=format&fit=crop&w=800&q=80',
        deskripsi_singkat: 'Pisang raja manis balut tepung krispi dengan limpahan keju cheddar & aren.',
        deskripsi: 'Potongan pisang raja manis alami dibalut adonan tepung renyah keemasan, ditaburi parutan keju cheddar melimpah, sirup gula aren kental, dan susu kental manis.',
        options_toppings: {
            title: 'Pilihan Topping Ekstra',
            multi: true,
            subtitle: 'Bisa Multi-pilih',
            choices: [
                { id: 'top_coklat', name: 'Ekstra Coklat Meses Ceres', desc: 'Manis coklat lumer', price: 4000 },
                { id: 'top_ice_cream', name: '1 Scoop Es Krim Vanilla', desc: 'Dingin manis creamy', price: 8000 }
            ]
        }
    }
];

export const WAITER_QUICK_ACTIONS = [
    { id: 'cutlery', label: 'Minta Sendok / Garpu / Piring', icon: '🍴', desc: 'Pelayan akan membawakan perlengkapan makan tambahan' },
    { id: 'bill', label: 'Minta Nota / Tagihan Pembayaran', icon: '🧾', desc: 'Pelayan akan membawakan rincian tagihan ke meja' },
    { id: 'clean', label: 'Bersihkan Meja', icon: '🧽', desc: 'Pelayan akan membersihkan meja Anda' },
    { id: 'water', label: 'Minta Air Putih / Es Batu', icon: '🧊', desc: 'Pelayan akan membawakan air mineral atau es batu' },
    { id: 'other', label: 'Panggil Pelayan ke Meja', icon: '🙋', desc: 'Pelayan akan langsung menuju ke meja Anda' },
];

export const AVAILABLE_TABLES = [
    { number: '01', name: 'Meja 01 (Indoor Depan)', token: '3KiGgju7Zb', capacity: '2 Orang' },
    { number: '02', name: 'Meja 02 (Indoor Tengah)', token: 'ZX822wMXX5', capacity: '4 Orang' },
    { number: '03', name: 'Meja 03 (Indoor Sofa)', token: '6q2VRvfV0r', capacity: '6 Orang' },
    { number: '04', name: 'Meja 04 (Outdoor Garden)', token: 'KT1DTfb8cD', capacity: '4 Orang' },
    { number: '05', name: 'Meja 05 (Outdoor Balcony)', token: 'namXX41oft', capacity: '2 Orang' },
];
