# Access Log Importer
## About
This is a small Laravel application that will fetch a server access log from an Amazon S3 bucket, parse the useragent data, and save the geographical IP and device information to the database. This could also easily be configured to fetch an access log from another location (local disk, another cloud storage platform, etc.)
## Installation
```
$ git clone https://github.com/jasonmajors/access-log-importer.git
```
Or download the zip and extract into a project directory.

Download the dependencies via [composer](https://getcomposer.org/):
```
$ composer install
```
In the project root, you'll need to copy `.env.example` into a `.env` file and set the database and AWS S3 credentials. For example, `.env` could look like this:
```
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_LOG_LEVEL=debug
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=homestead
DB_USERNAME=homestead
DB_PASSWORD=secret

AWS_KEY='MYAMAZONS3KEY'
AWS_SECRET='LongAWSs3TopSecretKeyDontShareWithPeople
AWS_REGION='us-east-1'
AWS_BUCKET='my-s3-bucket'

ACCESS_LOG='myaccesslog.log'
...
```
Generate an application key (alright, we don't really need to do this in the current iteration of the project, but it's a good practice)
```
$ php artisan key:generate
```
Run the database migrations: 
```
$ php artisan migrate
```
## Usage
To import the specified access log, run:
```
$ php artisan import:access-log
```
You can filter the import by visit start and end dates as well.
##### Examples:
No visits prior to 1/1/2016:
```
$ php artisan import:access-log --start='1/1/2016 12:00AM'
```
Only import visits between 10:00 AM on 12:00 PM on 1/1/2016
```
$ php artisan import:access-log --start='1/1/2016 10:00 AM' --end='1/1/2016 12:00 PM'
```
Only visits before 1/1/2015:
```
$ php artisan import:access-log --end='1/1/2015 12:00 AM'
```

## Testing
To run the tests, you'll need [PHPUnit](https://phpunit.de/getting-started.html) installed. Then from the project root, run:
```
$ phpunit
```

## Limitations
+ Fetching the geographical data for an IP address using free packages seems to be quite limited. In the event that a geographical attribute cannot be located, the name of the missing attribute will be logged to laravel.log
+ Determining a user's device based on the User-Agent string seems unreliable
+ Still need to write more unit tests