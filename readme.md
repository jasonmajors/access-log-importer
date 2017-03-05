# Access Log Importer
## About
This is a small Laravel application that will fetch a server access log from an Amazon S3 bucket, parse the useragent data, and save the GeoIP and device information to the database. This could also easily be configured to fetch an access log from another location (local disk, another cloud storage platform, etc.)
## Installation
```
$ git clone https://github.com/jasonmajors/access-log-importer.git
```
Or download the zip and extract into a project directory.
Download the dependencies via [composer](https://getcomposer.org/)::
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
php artisan key:generate
```

php artisan migrate
## Usage
TBD
## Testing
TBD
## Considerations
TBD