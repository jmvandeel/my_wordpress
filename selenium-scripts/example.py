from __future__ import print_function
import logging
import time
import datetime
from pyvirtualdisplay import Display
from selenium import webdriver
from selenium.webdriver.firefox.firefox_binary import FirefoxBinary 
logfile = open("/var/log/firefox", 'wb')
binary = FirefoxBinary('/usr/local/firefox/firefox', log_file=logfile)

display = Display(visible=0, size=(1024, 768))
display.start()

print('>> TEST START')
#print datetime.datetime.now().strftime("%y-%m-%d %H:%M")

browser = webdriver.Firefox(firefox_binary=binary, timeout=60)
browser.get("http://www.vandeel.com")
#print browser .page_source.encode('utf-8')
#assert "VANDEEL.COM" in browser.title

print('>> TEST ENDED')

driver.quit() # Quit the driver and close every associated window.
display.stop()