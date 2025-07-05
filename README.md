# Laravel FedEx Rate Estimator

A Laravel application for tracking FedEx labels and running estimations on FedEx shipping rates. This application integrates with the FedEx API to provide real-time shipping rates, package tracking, and label management. It also includes a Filament admin panel for easy management of shipments and labels.

## Features

- **FedEx Integration**: Connect to FedEx services for rate estimation and tracking
- **Label Management**: Track and manage FedEx shipping labels with detailed metadata
- **Rate Estimation**: Calculate shipping costs for different FedEx service types
- **Package Tracking**: Real-time tracking of FedEx packages
- **CSV Import**: Bulk import shipping data from CSV files
- **Email Notifications**: Send import summaries and tracking updates via email
- **Admin Panel**: Built with Filament for easy management of shipments and labels

## Installation

1. Clone the repository:
   ```bash
   git clone <repository-url>
   cd laravel-fedex-rate-estimator
   ```

2. Install dependencies:
   ```bash
   composer install
   npm install
   
   cd fexex-bot
   npm install
   ```

3. Set up environment:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. Configure your FedEx API credentials in `.env`:
   ```env
    FEDEX_TRACKING_CLIENT_ID=<your-fedex-tracking-client-id>
    FEDEX_TRACKING_CLIENT_SECRET=<your-fedex-tracking-client-id>
    FEDEX_RATES_CLIENT_ID=<your-fedex-rates-client-id>
    FEDEX_RATES_CLIENT_SECRET=<your-fedex-rates-client-secret>
    FEDEX_REPORTS_EMAILS=<csv of emails>
    FEDEX_MODE=<live|sandbox>
    FEDEX_ACCOUNT_NUMBER=<your-fedex-account-number>
    FEDEX_USERNAME=<your-fedex-username>
    FEDEX_PASSWORD=<your-fedex-password>
    NODE_LOCATION=<path-to-node>
   ```

5. Run migrations:
   ```bash
   php artisan migrate
   ```

6. Start the development server:
   ```bash
   composer run dev
   ```

## Usage

### Scheduled Job for Automatic Label Refresh

To automatically refresh FedEx labels and rate estimates on a schedule, add the following to your `routes/console.php` file:

```php
Schedule::job(new RefreshFedexLabelsJob(FedexImportMode::SCHEDULED))
    ->timezone('America/Chicago')
    ->cron('0 7-16 * * 1-5');
```

This schedule will run the job every hour from 7 AM to 4 PM, Monday through Friday, in the America/Chicago timezone.

### Manual Label Refresh via Admin Panel

1. Navigate to the Filament admin panel at `/admin`
2. Log in with your admin credentials
3. Go to the FedEx Labels section
4. Click the "Refresh Labels" button to manually import the latest labels and rate estimates

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

