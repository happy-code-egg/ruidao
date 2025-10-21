# PHP 文件上传配置说明

## 当前状态
- 前端限制：1.5MB
- 后端限制：1.5MB  
- PHP系统限制：2MB (upload_max_filesize)

## 如需支持20MB文件上传

### 方法1：修改 php.ini 文件

1. 找到PHP配置文件：`D:\PHP\7.2.31\php.ini`

2. 修改以下配置项：
```ini
upload_max_filesize = 25M
post_max_size = 30M
max_execution_time = 300
max_input_time = 300
memory_limit = 256M
```

3. 重启Web服务器

4. 修改应用配置：
   - 前端：将 `1.5 * 1024 * 1024` 改为 `20 * 1024 * 1024`
   - 后端：将 `max:1536` 改为 `max:20480`

### 方法2：使用 .user.ini 文件（如果支持）

在 `ema_api/public/` 目录创建 `.user.ini` 文件：
```ini
upload_max_filesize = 25M
post_max_size = 30M
max_execution_time = 300
max_input_time = 300
memory_limit = 256M
```

### 验证配置

创建测试文件 `test_config.php`：
```php
<?php
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "post_max_size: " . ini_get('post_max_size') . "\n";
?>
```

## 当前文件上传功能

✅ 支持1.5MB以下文件上传
✅ 支持多种文件格式
✅ 支持权限控制
✅ 支持文件预览和下载
✅ 文件存储在 `storage/app/public/customer_files/` 目录

## 注意事项

1. 修改PHP配置需要管理员权限
2. 配置修改后需要重启Web服务器
3. 大文件上传会消耗更多服务器资源
4. 建议根据实际需求设置合理的文件大小限制
