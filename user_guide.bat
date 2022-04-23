@ECHO OFF

rem update the user guide
D:\github\DevTools\officetopdf.exe "D:\github\DevTools-DEV\user guide.docx" "docs/user guide.pdf"

rem commit the changes

@git add .
@git commit -m"release notes"
