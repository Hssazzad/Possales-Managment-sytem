#!/bin/bash
# Hospital Billing System - Implementation Verification Script
# Run this to verify all files are in place

echo "========================================"
echo "Hospital Billing System - Verification"
echo "========================================"
echo ""

PASSED=0
FAILED=0
ROOT_DIR="/path/to/possales"  # Update to your project root

# Color codes
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Helper function to check file exists
check_file() {
    if [ -f "$1" ]; then
        echo -e "${GREEN}✓${NC} $2"
        ((PASSED++))
    else
        echo -e "${RED}✗${NC} $2 (File not found: $1)"
        ((FAILED++))
    fi
}

# Helper function to check directory exists
check_dir() {
    if [ -d "$1" ]; then
        echo -e "${GREEN}✓${NC} $2"
        ((PASSED++))
    else
        echo -e "${RED}✗${NC} $2 (Directory not found: $1)"
        ((FAILED++))
    fi
}

echo "📁 CHECKING DIRECTORY STRUCTURE"
echo "=================================="
check_dir "$ROOT_DIR/app/Http/Controllers/Billing" "Billing Controller directory"
check_dir "$ROOT_DIR/resources/views/invoice" "Invoice Views directory"
echo ""

echo "🎛️  CHECKING CONTROLLERS"
echo "=================================="
check_file "$ROOT_DIR/app/Http/Controllers/Billing/CreateInvoiceController.php" "CreateInvoiceController.php"
echo ""

echo "📦 CHECKING MODELS"
echo "=================================="
check_file "$ROOT_DIR/app/Models/MainCategory.php" "MainCategory.php"
check_file "$ROOT_DIR/app/Models/SubCategory.php" "SubCategory.php"
check_file "$ROOT_DIR/app/Models/TmpBill.php" "TmpBill.php"
check_file "$ROOT_DIR/app/Models/Invoice.php" "Invoice.php"
echo ""

echo "🏗️  CHECKING MIGRATIONS"
echo "=================================="
check_file "$ROOT_DIR/database/migrations/2024_04_20_000001_create_main_categories_table.php" "main_categories migration"
check_file "$ROOT_DIR/database/migrations/2024_04_20_000002_create_sub_categories_table.php" "sub_categories migration"
check_file "$ROOT_DIR/database/migrations/2024_04_20_000003_create_tbl_bill_tmp_table.php" "tbl_bill_tmp migration"
check_file "$ROOT_DIR/database/migrations/2024_04_20_000004_create_invoices_table.php" "invoices migration"
echo ""

echo "🌱 CHECKING SEEDERS"
echo "=================================="
check_file "$ROOT_DIR/database/seeders/BillingSeeder.php" "BillingSeeder.php"
echo ""

echo "🎨 CHECKING VIEWS"
echo "=================================="
check_file "$ROOT_DIR/resources/views/invoice/create.blade.php" "create.blade.php"
check_file "$ROOT_DIR/resources/views/invoice/print.blade.php" "print.blade.php"
echo ""

echo "🛣️  CHECKING ROUTES"
echo "=================================="
check_file "$ROOT_DIR/routes/billing.php" "billing.php"
echo ""

echo "📜 CHECKING JAVASCRIPT"
echo "=================================="
check_file "$ROOT_DIR/resources/js/billing-ajax-reference.js" "billing-ajax-reference.js"
echo ""

echo "📚 CHECKING DOCUMENTATION"
echo "=================================="
check_file "$ROOT_DIR/BILLING_QUICK_SETUP.md" "BILLING_QUICK_SETUP.md"
check_file "$ROOT_DIR/BILLING_SYSTEM_GUIDE.md" "BILLING_SYSTEM_GUIDE.md"
check_file "$ROOT_DIR/DATABASE_SCHEMA.sql" "DATABASE_SCHEMA.sql"
check_file "$ROOT_DIR/IMPLEMENTATION_SUMMARY.md" "IMPLEMENTATION_SUMMARY.md"
echo ""

echo "========================================"
echo "VERIFICATION RESULTS"
echo "========================================"
TOTAL=$((PASSED + FAILED))
echo -e "Total Files: $TOTAL"
echo -e "${GREEN}Passed: $PASSED${NC}"
echo -e "${RED}Failed: $FAILED${NC}"
echo ""

if [ $FAILED -eq 0 ]; then
    echo -e "${GREEN}✅ ALL FILES VERIFIED SUCCESSFULLY!${NC}"
    echo ""
    echo "📋 Next Steps:"
    echo "1. Update ROOT_DIR in web.php if needed"
    echo "2. Run: php artisan migrate"
    echo "3. Run: php artisan db:seed --class=BillingSeeder"
    echo "4. Visit: http://yourapp.test/invoice"
    exit 0
else
    echo -e "${RED}❌ SOME FILES ARE MISSING!${NC}"
    echo ""
    echo "Please check the errors above."
    exit 1
fi
