@ECHO OFF

rem update the user guide
D:\github\DevTools\docto -f "D:\github\DevTools-DEV\user guide.docx" -O "docs/user guide.pdf" -T wdFormatPDF

rem commit the changes

@git add .
@git commit -m"release notes"
