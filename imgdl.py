#!/usr/bin/python3
# -*- coding: utf-8 -*-

from os import rename
from sys import argv
import re
from urllib.request import urlretrieve
from urllib.request import urlopen
from urllib.request import build_opener, HTTPCookieProcessor
from urllib.parse import urlencode
from http.cookiejar import CookieJar
from imghdr import what
from bs4 import  BeautifulSoup
from PIL import Image
from pixivpy3 import *
import pymysql

dlDir = "./images/"
thumbDir = "./images/thumbnail/"

def thumbnail(input_file, output_file):
    size = 150
    img = Image.open(input_file)
    w,h = img.size
    l,t,r,b = 0,0,size,size
    new_w, new_h = size,size

    if w>=h:
        new_w = size * w // h
        l = (new_w - size) // 2
        r = new_w - l
    else:
        new_h = size * h // w
        t = (new_h - size) // 2
        b = new_h - t

    img = img.resize((new_w, new_h), Image.ANTIALIAS)
    img = img.crop((l,t,r,b))
    img.save(thumbDir + output_file, quality=100, optimize=True)

def regImg(loc,orig,thum,type):
    conn = pymysql.connect(host='127.0.0.1',user='',
            passwd='',db='',charset='utf8')
    cur = conn.cursor()
    cur.execute("INSERT INTO images (loc,orig,thum,type) VALUES (\"%s\", \"%s\", \"%s\", \"%s\")", (loc,orig,thum,type))
    cur.connection.commit()

def main():
    html = urlopen(argv[1])
    bsObj = BeautifulSoup(html)
    #print(bsObj.title.string)
    twi = re.compile('https:\/\/twitter.com\/[a-zA-Z0-9_]+\/status\/\d+')
    nic = re.compile('http:\/\/seiga.nicovideo.jp\/seiga\/[a-zA-Z0-9]+')
    pix = re.compile('http:\/\/www.pixiv.net\/member_illust.php\?mode=medium\&illust_id=[0-9]+')

    if twi.match(argv[1]):
        images = bsObj.find("div", {"class": "AdaptiveMedia-container      js-adaptive-media-container          "}).findAll("div", {"class": "AdaptiveMedia-photoContainer js-adaptive-photo "})
        for item in images:
            imageLoc = item.find("img")["src"]
            #print(item.find("img")["src"])
            urlretrieve(imageLoc , dlDir + "twi" + imageLoc[28:])
            loc = dlDir+"twi"+imageLoc[28:]
            thumb = "thumb_twi" + imageLoc[28:]
            type = what(loc)
            thumbnail(loc, thumb)

    elif nic.match(argv[1]):
        opener = build_opener(HTTPCookieProcessor(CookieJar()))
        post = {
            'mail_tel': '',
            'password': ''
        }
        data = urlencode(post).encode("utf_8")
        response = opener.open('https://secure.nicovideo.jp/secure/login', data)
        response.close()

        image_id = argv[1][34:]
        with opener.open('http://seiga.nicovideo.jp/image/source?id=' + image_id) as response:
            bsObj = BeautifulSoup(response)
            imageLoc = bsObj.find("div", {"class": "illust_view_big"}).find("img")["src"]
            dlLoc = dlDir + "nic" + image_id
            urlretrieve('http://lohas.nicoseiga.jp' + imageLoc, dlLoc)
            type = what(dlLoc)
            loc = dlLoc + "." + type
            rename(dlLoc, loc)
            thumb = "thumb_nico"+image_id+"."+type
            thumbnail(loc, thumb)
    
    elif pix.match(argv[1]):
        image_id = re.search('\d+', argv[1]).group()

        api = PixivAPI()
        api.login("", "")
        json_result = api.works(int(image_id))
        imageLoc = json_result.response[0].image_urls['large']
        opener = build_opener(HTTPCookieProcessor(CookieJar()))
        post = {
            'pixiv_id' : '',
            'pass'     : ''
        }
        data = urlencode(post).encode("utf_8")
        opener.addheaders = [('User-agent', 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; SV1)'),('Referer', '')]
        response = opener.open('https://www.secure.pixiv.net/login.php', data)
        response.close()
        opener.addheaders[1] = ('Referer', argv[1])
        loc = dlDir + "pix" + imageLoc.split("/")[-1]
        fp = open(loc, "wb")
        fp.write(opener.open(imageLoc).read())
        fp.close()
        type = what(loc)
        thumb = "thumb_pix"+imageLoc.split("/")[-1]
        thumbnail(loc, thumb)
        #with opener.open(imageLoc):
        #    urlretrieve(imageLoc, dlLoc)
        #    thumbnail(dlLoc, "thumb_pix" + image_id + imageLoc[-3:])
    
    regImg(loc, argv[1], "./images/thumbnail/"+thumb, type)

if __name__ == '__main__' :
    main()
