# Q3 - User Tracking System

## License Application & License API

User Tracking system which in turn provides / covers the following points like concurrent user tracking, different devices.
A license module is developed in the name of user tracking system which can be reused as it is written as an API.  

### Prerequisites

1. PHP 7.3.2
2. MYSQL 5.5.15

### Installation For FrontEnd APP, API & FRAMEWORK

1. Download the license application (licenseapp), license api (licenseapi) & framework (framework) folder from https://github.com/kgtechcrew/Q3.git
2. Unpack the downloaded file to a document root folder ( htdocs folder) .
3. Run the license.sql file in the localhost mysql server ( Database with relevant tables will be created )
4. Access the application using the following url http://localhost/Q3/licenseapp to view the frontend application
5. Perform the login
6. Use the below emails as username for login
     balu.p@kgisl.com
     dineshkumar.devaraj@kgisl.com
     srinivasan.k@kgisl.com
     dhanakumar.m@kgisl.com
     kanagaraj.r@kgisl.com
     tamilselvan.p@kgisl.com
     gowthamraj.v@kgisl.com
     sathishkanna.s@kgisl.com
     sathiyaraj.r@kgisl.com
     mahendran.k@kgisl.com
     santhosh.s@kgisl.com
7. Use the following password irrespective of all the logins -  Kgisl@123
8. Concurrent Users Restriction Count - 2 and Maximum Allowed login Per User Count - 3 is kept for testing purposes in table( This can be changed in lgt_license_global_table table in database)

## Rest API
API Authentication - This API is authenticated using JWT token concept inorder to increase the security of the API, It will prevent API attacks.

### Prerequisites

1. PHP 7.3.2
2. MYSQL 5.5.15
