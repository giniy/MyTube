# MyTube - Video Sharing Platform  

🚀 **A YouTube-like web application** built with PHP, MySQL, and JavaScript for uploading, sharing, and watching videos.  

---

## **📌 Features**  
✅ **User Authentication** (Login/Signup)  
✅ **Video Upload & Streaming** (MP4/WebM)  
✅ **Subscription System** (Coming Soon)  
✅ **Like/Comment Functionality**  
✅ **Responsive Design** (Works on Mobile & Desktop)  

---

## **🛠 Installation**  

### **Prerequisites**  
- PHP ≥ 8.0  
- MySQL ≥ 5.7  
- Apache/Nginx  
- Composer (for dependencies)  

### **Setup Steps**  
1. **Clone the repository**  

bash

```git clone https://github.com/giniy/mytube.git```

```cd mytube```

### Configure Database

Import ```mytube.sql``` (from /database) into phpMyAdmin.

Update includes/config.php with your DB credentials.

```php -S localhost:8000```

Open http://localhost:8000 in your browser.

### Existing users:
```
Username: root
Password: 123456
```

### Upload File Limit: 
100MB (can be set up in php.ini or .htaccess)

### **📁 Project Structure**

```
mytube/
├── 📂 auth/
├── 📂 database/
├── 📂 includes/
├── 📂 static/
├── 📂 uploads/
├── 📄 .htaccess
├── 📄 about.php
├── 📄 comments.php
├── 📄 contact.php
├── 📄 copyright.php
├── 📄 edit_topic.php
├── 📄 feedback.php
├── 📄 forum.php
├── 📄 functions.php
├── 📄 guidelines.php
├── 📄 help.php
├── 📄 index.php
├── 📄 like_video.php
├── 📄 moderate_topic.php
├── 📄 new_topic.php
├── 📄 privacy.php
├── 📄 README.md
├── 📄 search.php
├── 📄 share_video.php
├── 📄 subscriptions.php
├── 📄 terms.php
├── 📄 upload.php
├── 📄 view_topic.php
```

## Homepage

![Home Page](./static/images/Screenshot4.jpg)
![Login Page](./static/images/Screenshot1.jpg)

## Screenshots

![Screenshot 1](./static/images/Screenshot6.jpg)
![Screenshot 1](./static/images/screenshot3.jpg)
![Screenshot 2](./static/images/Screenshot5.jpg)

### License
📜 License
MIT © Gulab
MIT License

Copyright (c) 2025 Giniy & Gulab

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights  
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell  
copies of the Software, and to permit persons to whom the Software is  
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in  
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR  
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,  
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE  
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER  
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,  
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN  
THE SOFTWARE.