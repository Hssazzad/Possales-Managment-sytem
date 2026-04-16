# PosSales Management System

A comprehensive Point of Sale (POS) and inventory management system built with Laravel, featuring complete CRUD operations, stock management, income tracking, and more.

## Features

### Core Management
- **Product Management**: Complete CRUD with bulk upload, image support, and expired product tracking
- **Stock Management**: Real-time stock tracking, low stock alerts, and comprehensive reporting
- **Sales Management**: Full POS functionality with payment tracking and receipt generation
- **Income Tracking**: Daily income reports with category-based analysis
- **Customer Management**: Customer database with transaction history
- **Supplier Management**: Supplier relationships and purchase order tracking

### Advanced Features
- **User Authentication**: Role-based access control with permissions
- **Multi-Location Support**: Rack, shelf, and bin organization
- **Reporting**: Comprehensive reports with print and CSV export
- **Government Design**: Clean, professional interface following government design patterns
- **Real-time Updates**: Live stock levels and inventory status

### Technical Features
- **Database Integration**: MySQL with proper migrations and relationships
- **Modern UI**: AdminLTE theme with custom styling
- **Responsive Design**: Mobile-friendly interface
- **Search & Filter**: Advanced search capabilities across all modules
- **Bulk Operations**: Efficient bulk upload and management tools

## Installation

### Prerequisites
- PHP 8.0+
- MySQL/MariaDB
- Composer
- Node.js & NPM

### Setup Instructions

1. **Clone the repository**
```bash
git clone https://github.com/yourusername/possales-management-system.git
cd possales-management-system
```

2. **Install dependencies**
```bash
composer install
npm install
npm run build
```

3. **Environment setup**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Configure database**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=possales
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

5. **Run migrations and seeders**
```bash
php artisan migrate
php artisan db:seed
```

6. **Start the application**
```bash
php artisan serve
```

## Default Credentials

After running the seeders, you can login with:
- **Email**: admin@example.com
- **Password**: password

## Modules Overview

### Product Management
- Add, edit, delete products
- Bulk product upload via CSV
- Product categorization
- Image upload support
- Expired product tracking
- Stock quantity management

### Stock Management
- Real-time stock monitoring
- Low stock alerts (threshold-based)
- Stock movement tracking
- Comprehensive stock reports
- Print and CSV export functionality

### Sales & POS
- Complete POS interface
- Payment method support
- Receipt generation
- Sales history and reporting
- Customer integration

### Income Tracking
- Daily income summaries
- Category-based income analysis
- Comprehensive reporting
- Export functionality
- Visual income dashboards

### User Management
- Role-based access control
- Permission management
- User activity tracking
- Secure authentication

## Database Structure

The system includes the following main tables:
- `products` - Product catalog
- `sales` - Sales transactions
- `stock` - Inventory tracking
- `customers` - Customer information
- `suppliers` - Supplier data
- `income_categories` - Income categorization
- `users` - User management
- `roles` & `permissions` - Access control

## Technologies Used

- **Backend**: Laravel 11
- **Frontend**: Blade templates with AdminLTE
- **Database**: MySQL
- **Styling**: Custom CSS with government design patterns
- **JavaScript**: Vanilla JS with modern features
- **Build Tools**: Vite

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Support

For support and questions, please open an issue in the GitHub repository.

---

**Built with Laravel** - The PHP Framework For Web Artisans
