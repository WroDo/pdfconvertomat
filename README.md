# About
This simple webpage can be used in your intranet for converting PDF files without relying on external sites or paid software.

It provides two services:
* merging PDFs
* converting PDF to CSV (experimental, quality depends on how the PDF was built)
  
# ToDos
- As usual: refactor!

# Installing
- Use a LAMP with Poppler-Tools and ghostscript.
- Create log and uploads folders, make them writable for your webserver.
- Create your own etc/globals.php from the template
- Upload a company logo

# Usage
Just upload one or more files and hit the merge/convert button.

# Known Issues
Upload does not seem to work and apache (or nginx) states something like this:
```
[Thu Jan 25 16:05:59.507128 2024] [php:warn] [pid 30525] [client ::1:59876] PHP Warning:  POST Content-Length of 8956624 bytes exceeds the limit of 8388608 bytes in Unknown on line 0, referer: http://localhost/pdfconvertomat/index.php
```
-> set ```post_max_size``` and ```upload_max_filesize``` higher (php.ini).


#EOF
