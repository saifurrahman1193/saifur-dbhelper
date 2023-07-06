# Saifur-DBHelper
A DB Helper providing features like DB backup 

<a href="https://packagist.org/packages/saifur/dbhelper"><img src="https://img.shields.io/packagist/dt/saifur/dbhelper" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/saifur/dbhelper"><img src="https://img.shields.io/packagist/v/saifur/dbhelper" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/saifur/dbhelper"><img src="https://img.shields.io/packagist/l/saifur/dbhelper" alt="License"></a>

## Contents
- [Saifur-DBHelper](#saifur-dbhelper)
  - [Contents](#contents)
  - [Documentation, Installation, and Usage Instructions](#documentation-installation-and-usage-instructions)
    - [Commands](#commands)
    - [API](#api)
      - [Request: Server DB Full Backup](#request-server-db-full-backup)
      - [Request: Server DB Structure Backup](#request-server-db-structure-backup)
      - [Request: Server DB Data Backup](#request-server-db-data-backup)
      - [Request: Server DB Status](#request-server-db-status)
  - [Contributor](#contributor)
  - [Alternatives](#alternatives)
  - [License](#license)

## Documentation, Installation, and Usage Instructions
This package allows you to manage your logs.

Once installed you can do stuff like this:


### Commands

```
composer require saifur/dbhelper
composer dump-autoload
php artisan vendor:publish --tag=public --force
```

### API

<!-- make it postman document and markdown code -->

#### Request: Server DB Full Backup
- Method: POST
- URL: `http://localhost:8001/saifur/db-helper/db-backup/server-db-full-backup`
- Headers:
    - Authorization: Bearer \<your_token>
    - Content-Type: application/json
- Body:
    - form-data:
        | Key                          | Value         |
        |------------------------------|---------------|
        | except_tables[0]             | activity_log  |
        | except_tables[1]             | audit_trail   |
        | table_rules[0][table_name]   | audit_trail   |
        | table_rules[0][row_limit]    | 100           |
        | table_rules[0][order_by]     | id            |
        | table_rules[0][order_type]   | DESC          |
        | table_rules[1][table_name]   | activity_log  |
        | table_rules[1][row_limit]    | 100           |
        | table_rules[1][order_by]     | id            |
        | table_rules[1][order_type]   | DESC          |

#### Request: Server DB Structure Backup
- Method: POST
- URL: `http://localhost:8001/saifur/db-helper/db-backup/server-db-structure-backup`
- Headers:
    - Authorization: Bearer \<your_token>
    - Content-Type: application/json

#### Request: Server DB Data Backup
- Method: POST
- URL: `http://localhost:8001/saifur/db-helper/db-backup/server-db-data-backup`
- Headers:
    - Authorization: Bearer \<your_token>
    - Content-Type: application/json
- Body:
    - form-data:
        - except_tables[0]: activity_log
        - except_tables[1]: audit_trail
        - table_rules[0][table_name]: audit_trail
        - table_rules[0][row_limit]: 100
        - table_rules[0][order_by]: id
        - table_rules[0][order_type]: DESC
        - table_rules[1][table_name]: activity_log
        - table_rules[1][row_limit]: 100
        - table_rules[1][order_by]: id
        - table_rules[1][order_type]: DESC

#### Request: Server DB Status
- Method: POST
- URL: `http://localhost:8001/saifur/db-helper/db-backup/server-db-status`
- Headers:
    - Authorization: Bearer \<your_token>
    - Content-Type: application/json
- Body:
    - form-data:
        - view: html

## Contributor

- Md. Saifur Rahman


|[![Portfolio](https://img.shields.io/badge/Portfolio-%23009639.svg?style=for-the-badge&logo=Hyperledger&logoColor=white)](https://saifurrahman.my.canva.site) | [![CV](https://img.shields.io/badge/CV-%23009639.svg?style=for-the-badge&logo=DocuSign&logoColor=white)](https://docs.google.com/document/d/1txBCiMjPqH7GR8FDMQMAw09vemsB-nJb/edit?usp=sharing&ouid=113622980255867007734&rtpof=true&sd=true) | [![LinkedIn](https://img.shields.io/badge/linkedin-%230077B5.svg?style=for-the-badge&logo=linkedin&logoColor=white)](https://www.linkedin.com/in/saifurrahman1193/) | [![GitHub](https://img.shields.io/badge/github-%23121011.svg?style=for-the-badge&logo=github&logoColor=white)](https://github.com/saifurrahman1193/saifurrahman1193) | [![Stack Overflow](https://img.shields.io/badge/-Stackoverflow-FE7A16?style=for-the-badge&logo=stack-overflow&logoColor=white)](https://stackoverflow.com/users/14350717/md-saifur-rahman) | 
|-|-|-|-|-|
| [![Hackerrank](https://img.shields.io/badge/-Hackerrank-2EC866?style=for-the-badge&logo=HackerRank&logoColor=white)](https://www.hackerrank.com/saifur_rahman111) | [![Beecrowd](https://img.shields.io/badge/Beecrowd-%23009639.svg?style=for-the-badge&logo=Bugcrowd&logoColor=white)](https://www.beecrowd.com.br/judge/en/profile/18847) | [![LeetCode](https://img.shields.io/badge/LeetCode-000000?style=for-the-badge&logo=LeetCode&logoColor=#d16c06)](https://leetcode.com/saifurrahman1193) | [![YouTube](https://img.shields.io/badge/YouTube-%23FF0000.svg?style=for-the-badge&logo=YouTube&logoColor=white)](https://www.youtube.com/playlist?list=PLwJWgDKTF5-xdQttKl7cRx8Yhukv7Ilmg)| |

## Alternatives


## License
The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
