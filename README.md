# ✦ NOVA

## Modern E-Ticaret ve Mağaza Yönetim Platformu

NOVA, giyim mağazasının **e-ticaret, POS, stok, ürün, varyant, müşteri, ödeme, kasa, indirim, iade ve raporlama** süreçlerini tek yerde yönetmek için geliştirilmiş bir Laravel uygulamasıdır.

> **Tek platform. Tüm mağaza operasyonları.**

---

## NOVA Nedir?

NOVA yalnızca bir vitrin sitesi değildir. Müşteri alışverişi ile mağaza operasyonu aynı veritabanı ve iş kuralları üzerinden yürür.

```text
Ürün → Kategori / Marka → Varyant → Stok
  → Sepet / POS → Satış / Sipariş → Ödeme → Kasa
  → İade / Değişim → Raporlama → İşlem kayıtları
```

---

## Neden NOVA?

Amaç; modern arayüz, gerçek mağaza kuralları, yetkilendirme ve ilişkisel veri modelini aynı projede birleştirmektir.

```text
                    NOVA
                     │
       ┌─────────────┼─────────────┐
       │             │             │
       ▼             ▼             ▼
   E-Ticaret       Mağaza        Yönetim
     Sepet          POS          Ürün / Stok
     Sipariş        Ödeme        Marka / Kategori
     Favoriler      Kasa         İndirim / Rapor
```

---

## Öne Çıkan Özellikler

| Özellik | Açıklama |
| --- | --- |
| E-Ticaret | Koleksiyon, arama, sepet, ödeme, hesap |
| POS | Barkod / SKU ile mağaza satışı |
| Stok | Varyant bazlı miktar, rezerv, hareket kaydı |
| Varyant | Renk, beden / numara, SKU, barkod, fiyat |
| Marka / Kategori | Veritabanından dinamik menü ve filtre |
| İndirim | Yüzde veya sabit tutar; ürün, varyant, kategori, marka |
| Ödeme | Nakit, kart, diğer |
| Kasa | Açılış / kapanış ve kasa hareketleri |
| Gelir-Gider | Kasa dışı finansal kayıtlar |
| İade | Satışa bağlı iade; müşteri talebi + admin onay/red |
| Raporlar | Satış, stok, iade, kasa, müşteri, tedarikçi + CSV / Excel / PDF |
| Yetkilendirme | Personel rolleri ve sayfa bazlı erişim |
| Audit log | Kritik işlemlerin kaydı |
| Çok dil | İngilizce, Türkçe, Almanca, Fransızca |
| Karanlık mod | `.dark` sınıfı + sistem tercihi + manuel geçiş |

---

## Müşteri Tarafı

### Sayfalar ve akışlar

- Ana sayfa
- Koleksiyonlar: Kadın, Erkek, Çocuk, Spor, Yeni gelenler, İndirim, marka ve kategori slug’ları (`/shop/{department}/{category?}`)
- Ürün detayı ve varyant seçimi
- Marka ve indirim vitrinleri
- Arama
- Sepet (çekmece / panel)
- Favoriler
- Ödeme ve sipariş onayı
- Hesap: profil, şifre, adresler, siparişler, iade talepleri, görünüm ayarı
- Statik sayfalar: hakkında, kariyer, sürdürülebilirlik, iletişim, kargo, iade, SSS, beden rehberi, gizlilik, şartlar, çerezler

```text
Ürün keşfi → Detay → Varyant → Sepet → Ödeme → Sipariş → Takip / İade talebi
```

### Hesap

Giriş ve kayıt müşteri kaydına bağlıdır. Sipariş, adres ve iade talepleri yalnızca sahibi tarafından görülür.

---

## Yönetim Paneli

Giriş: `/admin/login` (kullanıcı adı + şifre, oturum tabanlı).

```text
Yönetim Paneli
│
├── Genel
│   └── Gösterge paneli
├── Mağaza
│   ├── Ürünler
│   ├── Kategoriler (üst menü, kapak görselleri)
│   ├── Markalar
│   ├── İndirimler
│   ├── Varyantlar
│   ├── Stok
│   ├── Stok hareketleri
│   └── Barkod
├── Satış
│   ├── POS
│   ├── Satışlar
│   └── İadeler
├── Kişiler
│   ├── Müşteriler
│   └── Tedarikçiler
├── Finans
│   ├── Kasa
│   ├── Kasa hareketleri
│   ├── Gelir-gider
│   └── Ödemeler
├── Raporlama
│   ├── Satış / Stok / İade / Kasa / Müşteri / Tedarikçi
│   └── Dışa aktarma (CSV, Excel, PDF)
├── Site
│   ├── Hakkında
│   ├── Yardım
│   └── Journal (yasal sayfalar)
└── Sistem
    ├── Roller ve kullanıcılar
    ├── İşlem kayıtları
    ├── Bildirimler
    ├── Profil
    └── Ayarlar
```

Menü, giriş yapan personelin rolüne göre filtrelenir.

---

## Ürün ve Varyant

Aynı ürün renk / beden (veya ayakkabı numarası) kombinasyonlarıyla `product_variants` üzerinden tutulur.

Her varyant: SKU, barkod, renk, beden, fiyat, aktiflik, stok.

Ürün ayrıca KDV oranı (`vat_rate`: 0, 1, 8, 10, 18, 20), para birimi, katalog kodu, görseller ve öznitelikler taşır.

---

## Kategori ve Marka

Kategoriler hiyerarşiktir (`parent_id`). Aktif kategoriler mağaza menüsüne ve `/shop/...` filtrelerine yansır. Üst menüye ekleme ve ana sayfa kapakları (hero, kadın, erkek, koleksiyonlar) panelden yönetilir.

Markalar oluşturulabilir, düzenlenebilir, aktif/pasif yapılabilir; logolu marka sayfası `/brands/{brand}` üzerinden açılır.

---

## Stok

Stok varyant üzerindedir. `stocks` tablosunda `quantity`, `reserved_quantity`, `minimum_quantity` tutulur.

Hareket türleri:

- `purchase` — giriş
- `sale` — satış çıkışı
- `return` — iade girişi
- `exchange_in` / `exchange_out` — değişim
- `adjustment_in` / `adjustment_out` — manuel düzeltme

Kurallar: negatif stok varsayılan olarak kapalıdır; satış öncesi stok kontrol edilir; iade stoğu geri ekler; kritik yazmalar transaction içindedir.

---

## Fiyat ve KDV

Fiyatlar `DECIMAL(12,2)` ile tutulur. Backend `Price::breakdown` ile brüt fiyattan net ve KDV ayırır (bcmath). Varsayılan KDV %20’dir.

Mağaza ayarlarında para birimi varsayılanı **TRY**’dir. Müşteri vitrininde fiyatlar şu an **EUR** olarak biçimlenir.

---

## İndirim

Türler: `percent`, `fixed`.

Kapsam: ürün, varyant, kategori, marka. Başlangıç / bitiş tarihi ve aktiflik kontrol edilir. Checkout ve POS, istemciden gelen indirim tutarına güvenmez; tutar sunucuda yeniden hesaplanır.

---

## Sepet, Favori, Sipariş

Sepet müşteriye bağlıdır. Checkout sırasında ürün, varyant, stok, fiyat, indirim ve toplam backend’de doğrulanır. Standart kargo ücretsiz, ekspres **15** birimdir.

Sipariş kalemleri o anki ürün adı, SKU, renk, beden, birim fiyat ve indirim adıyla kopyalanır; sonraki fiyat değişimi geçmişi etkilemez.

Sipariş numarası örneği: `NV-20260915-0001`

Favoriler ürüne bağlıdır ve müşteriye özeldir.

---

## POS, Ödeme, Kasa

Kasiyer akışı: barkod / SKU → ürün → varyant → sepet → ödeme → satış.

Başarılı satış: sipariş + kalemler → stok çıkışı → ödeme kaydı → (nakitte) kasa hareketi → audit.

Kasa açılış ve kapanış ekranları vardır. Nakit satış kasaya yansır; nakit iade kasayı azaltır. Gelir-gider modülü kasa dışı hareketler içindir.

---

## İade ve Değişim

İade mevcut sipariş üzerinden yapılır. Aynı kalemin mükerrer iadesi engellenir.

Müşteri panelinden talep oluşturmak doğrudan para iadesi değildir:

```text
Talep → Yönetici incelemesi → Onay / Red → Stok / ödeme / kasa
```

Değişim (`exchanges`) veri modelinde ve stok hareketlerinde vardır; ayrı bir panel menüsü henüz yok.

İade numarası örneği: `RT-20260915-0001`

---

## Kullanıcı ve Roller

Müşteri ile personel ayrıdır.

Personel rolleri (`StaffRole`):

| Rol | Erişim özeti |
| --- | --- |
| Super Admin | Tüm sayfalar ve ayarlar |
| Store Manager | Operasyon, rapor, kullanıcı; audit ve sistem ayarları yok |
| Cashier | POS, satış, müşteri, iade |
| Warehouse Staff | Ürün, stok, barkod |

Yetki, Laravel Policy / Gate değil; `EnsureAdminAuthenticated` + `EnsureAdminPageAccess` ve rol izin listesidir. API admin uçları aynı oturumu kullanır (Sanctum yok).

---

## Güvenlik

- Laravel session authentication (müşteri ve admin ayrı oturum)
- CSRF
- Giriş throttle (`5/dakika`)
- Password hashing
- Eloquent / query builder (SQL injection yüzeyini azaltır)
- Mass assignment: model `Fillable` öznitelikleri
- Sipariş ve iade sahiplik kontrolü
- Sunucu tarafı fiyat, stok ve indirim doğrulaması
- Satış / iade / stok için database transaction

---

## İşlem Kayıtları

`audit_logs`: kullanıcı, eylem, kaynak tipi/id, eski/yeni değer, IP, user-agent, tarih.

---

## Gösterge ve Raporlar

Panelde satış, sipariş, müşteri, ürün, stok, iade ve kasa özetleri vardır.

Raporlar: satış, stok, iade, kasa, müşteri, tedarikçi. Dışa aktarma: CSV, Excel, PDF.

---

## Teknik Mimari

```text
Müşteri arayüzü / Yönetim paneli
        │
   Blade + Tailwind v4
        │
   JavaScript (fetch) + Vite
        │
   Web rotaları  +  REST /api (web oturumu)
        │
   Controller
        │
   Support (Catalog, AdminStore, DatabaseRecords, Price)
        │
   Eloquent ORM
        │
   PostgreSQL (UUID birincil anahtarlar)
```

API, ayrı token API’si değildir; `routes/api.php` web middleware ile oturum kullanır.

---

## Teknolojiler

**Backend:** PHP 8.3+, Laravel 13, REST uçları, PHPUnit 12

**Veritabanı:** PostgreSQL (geliştirme varsayılanı). Testler in-memory SQLite kullanır. UUID’ler Laravel `HasUuids` ile üretilir.

**Frontend:** Blade, Tailwind CSS v4, Vite 8, vanilla JS (`fetch`). Axios yok.

**Yazı tipleri:** Inter (gövde), Source Serif 4 (başlık). İkonlar inline SVG.

---

## API (özet)

Önek: `/api`

Herkese açık (oturum çereziyle sepet/favori):

```http
GET    /api/catalog
GET    /api/catalog/{product}
GET    /api/brands
GET    /api/categories
GET    /api/discounts
GET    /api/search
GET|POST|PATCH|DELETE  /api/cart
GET|POST               /api/wishlist
POST                   /api/checkout
GET                    /api/orders
GET                    /api/orders/{order}
GET|POST               /api/return-requests
```

Admin (aynı oturum + sayfa yetkisi): ürün, varyant, kategori, marka, indirim, POS, satış, iade, müşteri, tedarikçi, kasa, stok, ödeme, arama.

---

## Veri Modeli

Ana tablolar: `users`, `roles`, `permissions`, `products`, `categories`, `brands`, `product_variants`, `product_images`, `stocks`, `stock_movements`, `customers`, `customer_addresses`, `carts`, `wishlists`, `orders`, `order_items`, `payments`, `cash_registers`, `cash_transactions`, `returns`, `return_items`, `exchanges`, `suppliers`, `discounts`, `audit_logs`, `storefront_covers`.

---

## Kurulum

Gereksinimler: PHP 8.3+, Composer, PostgreSQL, Node.js, NPM, Git.

```bash
git clone <repository-url>
cd NOVA
composer install
```

Windows: `copy .env.example .env`  
Linux / macOS: `cp .env.example .env`

```bash
php artisan key:generate
```

`.env`:

```env
APP_NAME=NOVA
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=nova
DB_USERNAME=postgres
DB_PASSWORD=your_password
APP_LOCALE=tr
```

```bash
php artisan migrate --seed
npm install
npm run dev          # geliştirme
# npm run build      # üretim
php artisan storage:link
php artisan serve
```

Müşteri: [http://localhost:8000](http://localhost:8000)  
Admin: [http://localhost:8000/admin/login](http://localhost:8000/admin/login)

### Demo hesaplar (seeder)

| Rol | Kullanıcı | Şifre |
| --- | --- | --- |
| Müşteri | `ada@nova.example` | `password123` |
| Mağaza müdürü | `deniz` | `secret123` |
| Kasiyer | `ayse` / `mert` | `secret123` |
| Depo | `ece` | `secret123` |

---

## Test

```bash
php artisan test
```

Kapsanan alanlar: kimlik doğrulama, yetki, katalog, kategori, marka, stok, sepet, favori, sipariş, POS, kasa, iade talebi, indirim, audit, locale, kapak görselleri, API sahiplik izolasyonu.

---

## Proje Durumu

| Modül | Durum |
| --- | --- |
| Müşteri arayüzü | Tamamlandı |
| Yönetim paneli | Tamamlandı |
| REST uçları (oturum) | Tamamlandı |
| PostgreSQL şema | Tamamlandı |
| POS / sipariş / kasa / iade | Tamamlandı |
| Rapor dışa aktarma | Tamamlandı |
| Çok dil + karanlık mod | Tamamlandı |
| Değişim arayüzü | Veri modeli var, ayrı menü yok |
| Online ödeme sağlayıcısı | Yok |
| Test / iyileştirme | Devam ediyor |

---

## Gelecek

- E-posta / SMS bildirimleri
- Kupon ve gelişmiş kampanya
- Kargo ve sanal POS entegrasyonu
- Çoklu mağaza / depo
- Docker ve production Nginx + PHP-FPM

---

**Laravel · PostgreSQL · Blade · Tailwind CSS · JavaScript**

> Gerçek mağaza operasyonlarını modern yazılım mimarisiyle birleştirmek için geliştirildi.
