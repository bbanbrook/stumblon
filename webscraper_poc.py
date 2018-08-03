# encoding=utf8 

from bs4 import BeautifulSoup
import urllib2
import unicodedata
import sys  
from time import mktime
import time
from datetime import datetime

reload(sys)  
sys.setdefaultencoding('utf8')

url = "https://widgets.healcode.com/widgets/schedules/2321042d1aa/load_markup?callback=jQuery18109710361390110409_1530253081931&options%5Blive_preview%5D=false&_=1530253082280"

response = urllib2.urlopen(url)
response_u = response.read().decode('unicode-escape')

#DEBUG: check response type if needed
#print type(response_u)

#DEBUG: if response contains illegal char's
#response_u = response_u.encode("utf-8").replace("\xe2\x80\x93","")

soup = BeautifulSoup(response_u, 'lxml')
#print soup.prettify()

#this is the session array
sess_list = soup.find_all(attrs={"class": "bw-session__info"})

#Function: get session detail
def lookup_desc(val):
    sess_detail_list = soup.find_all(attrs={"class": "bw-session__details"})
    for sess_detail in sess_detail_list:
        for element in sess_detail.find_all(attrs={"class": "bw-session__full-title"}):
            #print element.get_text(strip=True)
            if (element.get_text(strip=True) == val):
                for desc in sess_detail.find_all(attrs={"class": "bw-session__description"},limit=1):
                    return desc.get_text(strip=True)

#list of class names to search for
list = lambda x: x and x.startswith(('bw-session__staff'
                            , 'hc_starttime'
                            , 'hc_starttime'
                            , 'hc_endtime'
                            , 'bw-session__name'
                            , 'datetime'))

#init counter in case needed
count = 0

#loop over session info
for sess in sess_list:
    count += 1
    
    #init variables to scrape
    time_s = ""
    time_e = ""
    #DEBUG: print count
    ilist = "" + str(count)
    #loop over schedule item info
    for element in sess.find_all(attrs={"class": list}): 
        ilist += "|" + element.get_text(strip=True)
        
        #if(element.name == "time"):# == # + u"..."#element.datetime
        #    ilist += u"|" + element["datetime"]
        if(element.name == "time"):
            if (element['class']==["hc_starttime"]):
                #print element['datetime']
                ilist += u"|" + element['datetime']
                time_s = element["datetime"]
            if (element['class']==["hc_endtime"]):
                #print element['datetime']
                ilist += u"|" + element["datetime"]
                time_e = element["datetime"]
        #if element.find_all(attrs={"class": "hc_endtime"}):
        #    ilist += u"|" + element["datetime"]
        #    time_e = element["datetime"] 
        #get session detail info
        #DEBUG: print element.find_all()
        if element.find_all(attrs={"class": "bw-session__type"}):
            element.contents[1].clear()
            ilist += u"|" + lookup_desc(element.get_text(strip=True) + " Description")
            #lookup_desc(element.get_text(strip=True) + " Description")#string="bw-session__name")
    
    # convert times for durations
    fmt = '%Y-%m-%dT%H:%M' #2018-07-04T14:0003:30 PM
    t1 = datetime.strptime(time_s, fmt)
    t2 = datetime.strptime(time_e, fmt)
    
    # get date
    ilist += "|" + '{:%Y-%m-%d}'.format(t1)
    
    # Convert to Unix timestamp to calc duration in mins
    t1 = time.mktime(t1.timetuple())
    t2 = time.mktime(t2.timetuple())
    ilist += "|" + str(int(t2-t1) / 60)
    #output list
    print ilist
    