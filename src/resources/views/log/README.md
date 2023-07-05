# Saifur Log Viewer
## Frontend
* single page app
* Tech:
  * vue
  * vuetify
  * material ui icon
## Backend
* Tech:
  * laravel api

## Security

### Middleware
* SaifurLogViewerMiddleware

#### kernel
* app\Http\Kernel.php
```
    'SaifurLogViewerMiddleware' => \App\Http\Middleware\SaifurLogViewerMiddleware::class,
```

### .env
* SAIFUR_LOGVIEWER_MIDDLEWARE=1
  * if you want that the log viewer must be authenticated


```
SAIFUR_LOGVIEWER_MIDDLEWARE=1
```
### config/app.php
```
    'saifur_logviewer_middleware' => env('SAIFUR_LOGVIEWER_MIDDLEWARE', '0'),
```

### DB

#### Table
* users
#### Column
* log_viewer
  * 0 = default
  * 1 = assign 
    * who can be able to access saifur log viewer

```
`log_viewer` tinyint(4) DEFAULT 0,
```

