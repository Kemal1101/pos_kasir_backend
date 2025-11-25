# Views Structure Documentation

Struktur views aplikasi POS Kasir telah dirapikan untuk meningkatkan maintainability dan organisasi kode.

## 📁 Struktur Folder

```
resources/views/
│
├── auth/                       # Halaman autentikasi
│   └── login.blade.php         # Halaman login
│
├── pages/                      # Halaman utama aplikasi
│   └── dashboard.blade.php     # Dashboard overview
│
├── categories/                 # Manajemen kategori
│   └── index.blade.php         # Daftar & CRUD kategori
│
├── products/                   # Manajemen produk
│   └── index.blade.php         # Daftar & CRUD produk
│
├── users/                      # Manajemen user
│   └── index.blade.php         # Daftar & CRUD user
│
├── reports/                    # Laporan
│   ├── sales.blade.php         # Laporan penjualan (web view)
│   └── sales-report-pdf.blade.php  # Template PDF laporan
│
├── layouts/                    # Layout template
│   └── app.blade.php           # Main layout dengan sidebar
│
└── components/                 # Komponen reusable
    ├── modals/                 # Modal components
    │   ├── add-category.blade.php
    │   ├── edit-category.blade.php
    │   ├── delete-category.blade.php
    │   ├── add-product.blade.php
    │   ├── edit-product.blade.php
    │   ├── delete-product.blade.php
    │   ├── add-user.blade.php
    │   └── edit-user.blade.php
    │
    └── pagination/             # Pagination components
        └── custom.blade.php
```

## 🔄 Mapping Controller → View

| Controller | Method | View Path |
|------------|--------|-----------|
| `AuthController` | `show()` | `auth.login` |
| `DashboardController` | `index()` | `pages.dashboard` |
| `DashboardController` | `category()` | `categories.index` |
| `ProductController` | `index()` | `products.index` |
| `UsersController` | `manage()` | `users.index` |
| `SalesReportController` | `index()` | `reports.sales` |
| `SalesReportController` | `exportPdf()` | `reports.sales-report-pdf` |

## 📝 Konvensi Penamaan

- **Folder name**: plural, lowercase (categories, products, users)
- **File name**: descriptive, kebab-case (sales-report-pdf.blade.php)
- **Main pages**: gunakan `index.blade.php` untuk halaman utama
- **Components**: group by type dalam subfolder

## 🎯 Best Practices

1. **Separation of Concerns**: Setiap module memiliki folder sendiri
2. **Reusable Components**: Modal dan pagination di folder components
3. **Consistent Naming**: Mengikuti konvensi Laravel standar
4. **Easy Navigation**: Struktur folder yang jelas dan intuitif

## 🔧 Maintenance

Saat menambahkan view baru:
1. Tentukan kategori (auth/pages/module-specific)
2. Letakkan di folder yang sesuai
3. Update mapping di dokumentasi ini
4. Pastikan path di controller sesuai dengan struktur folder

---
Last Updated: November 25, 2025
