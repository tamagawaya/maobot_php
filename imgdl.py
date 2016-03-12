#!/usr/bin/python3
# -*- coding: utf-8 -*-

import sys
import re
import os
from urllib.request import urlretrieve
from urllib.request import urlopen
from urllib.request import build_opener, HTTPCookieProcessor
from urllib.parse import urlencode
from http.cookiejar import CookieJar
from configparser import SafeConfigParser
from imghdr import what
from bs4 import  BeautifulSoup
from PIL import Image
import pymysql

dlDir = "./images/"
thumbDir = "./images/thumbnail/"
thumb_lDir = "./images/thumbnail_l/"

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

    thu = img.resize((new_w, new_h), Image.ANTIALIAS)
    thu = thu.crop((l,t,r,b))
    thu.save(thumbDir + output_file, quality=100, optimize=True)

    thu = img.resize((w*300/h, 300), Image.ANTIALIAS)
    thu.save(thumb_lDir + output_file, quality=100, optimize=True)

def regImg(loc,orig,thum,type):
    conn = pymysql.connect(host='127.0.0.1',user='',
            passwd='',db='',charset='utf8')
    cur = conn.cursor()
    cur.execute("INSERT INTO images (loc,orig,thum,type) VALUES (\"%s\", \"%s\", \"%s\", \"%s\")", (loc,orig,thum,type))
    cur.connection.commit()
    cur.close()
    conn.close()

def readConfig():
    config = SafeConfigParser()
    if os.path.exists('imgdl.ini'):
        config.read('imgdl.ini')
    else:
        print("No Configuration File.")
        sys.exit(2)

    try:
        nicouser = config.get('nicoseiga.jp', 'user')
        nicopass = config.get('nicoseiga.jp', 'pass')
    except Exception as e:
        return "error: could not read nico configuration." + e

    try:
        pixiuser = config.get('pixiv.net', 'user')
        pixipass = config.get('pixiv.net', 'pass')
    except Exception as e:
        return "error: could not read pixiv configuration." + e

    return nicouser, nicopass, pixiuser, pixipass

def main():
    orig_url = sys.argv[1]
    html = urlopen(orig_url)
    nicouser, nicopass, pixiuser, pixipass = readConfig()
    bsObj = BeautifulSoup(html)
    twi = re.compile('https:\/\/twitter.com\/[a-zA-Z0-9_]+\/status\/\d+')
    nic = re.compile('http:\/\/seiga.nicovideo.jp\/seiga\/[a-zA-Z0-9]+')
    pix = re.compile('http:\/\/www.pixiv.net\/member_illust.php\?mode=medium\&illust_id=[0-9]+')
    image_format = ["jpg", "jpeg", "gif", "png"]
    
    if twi.match(orig_url):
        #print(orig_url)
        #images = bsObj.find("div", {"class": "AdaptiveMedia-container      js-adaptive-media-container          "}).findAll("div", {"class": "AdaptiveMedia-photoContainer js-adaptive-photo "})
        #images = bsObj.find("div", {"class": re.compile("^(AdaptiveMedia-container)\s+(js-adaptive-media-container)\s*")}).findAll("div", {"class" : re.compile("^(AdaptiveMedia-photoContainer)\s+(js-adaptive-photo)\s*")})
        images = bsObj.find("div", {"class": "AdaptiveMedia-container"}).findAll("div", {"class": "AdaptiveMedia-photoContainer"})
        for item in images:
            imageLoc = item.find("img")["src"]
            urlretrieve(imageLoc , dlDir + "twi" + imageLoc[28:])
            loc = dlDir+"twi"+imageLoc[28:]
            thumb = "thumb_twi" + imageLoc[28:]
            type = what(loc)
            thumbnail(loc, thumb)
            regImg(loc, orig_url, "./images/thumbnail/"+thumb, type)
            print(thumb_lDir+thumb)

    elif nic.match(orig_url):
        opener = build_opener(HTTPCookieProcessor(CookieJar()))
        post = {
            'mail_tel': nicouser,
            'password': nicopass
        }
        data = urlencode(post).encode("utf_8")
        response = opener.open('https://secure.nicovideo.jp/secure/login', data)
        response.close()

        image_id = orig_url[34:]
        with opener.open('http://seiga.nicovideo.jp/image/source?id=' + image_id) as response:
            bsObj = BeautifulSoup(response)
            imageLoc = bsObj.find("div", {"class": "illust_view_big"}).find("img")["src"]
            dlLoc = dlDir + "nic" + image_id
            urlretrieve('http://lohas.nicoseiga.jp' + imageLoc, dlLoc)
            type = what(dlLoc)
            loc = dlLoc + "." + type
            os.rename(dlLoc, loc)
            thumb = "thumb_nico"+image_id+"."+type
            print(thumb_lDir+thumb)
            thumbnail(loc, thumb)
        regImg(loc, orig_url, "./images/thumbnail/"+thumb, type)
    
    elif pix.match(orig_url):
        image_id = re.search('\d+', orig_url).group()
        opener = build_opener(HTTPCookieProcessor(CookieJar()))
        post = {
            'mode'     : 'login',
            'return_to': '/',
            'pixiv_id' : pixiuser,
            'pass'     : pixipass,
            'skip'     : '1'
        }
        data = urlencode(post).encode("utf_8")
        opener.addheaders = [('User-agent', ':Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.4 (KHTML, like Gecko) Ubuntu/12.10 Chromium/22.0.1229.94 Chrome/22.0.1229.94 Safari/537.4'),('Referer', '')]
        response = opener.open('https://www.secure.pixiv.net/login.php', data)
        response.close()
        page = opener.open(orig_url)
        bsObj = BeautifulSoup(page)
        img = bsObj.find("img", {"class": "original-image"})
        a = bsObj.find("a", {"class": " _work multiple "})
        imageLocs = []
        if img is not None:
            imageLocs.append(img["data-src"])
        elif a is not None:
            manga = BeautifulSoup(opener.open("http://www.pixiv.net/member_illust.php?mode=manga&illust_id="+image_id))
            images = manga.findAll("img", {"data-filter": "manga-image"})
            for img in images:
                imageLocs.append(img["data-src"])

        opener.addheaders[1] = ('Referer', orig_url)
        for imageLoc in imageLocs:
            loc = dlDir + "pix" + imageLoc.split("/")[-1]
            fp = open(loc, "wb")
            fp.write(opener.open(imageLoc).read())
            fp.close()
            type = what(loc)
            thumb = "thumb_pix"+imageLoc.split("/")[-1]
            thumbnail(loc, thumb)
            regImg(loc, orig_url, "./images/thumbnail/"+thumb, type)
            print(thumb_lDir+thumb)

    elif orig_url.split(".")[-1] in image_format:
        filename = "_".join(orig_url.split("/")[-2:])
        loc = dlDir + filename
        thumb = "thumb_"+filename
        urlretrieve(orig_url , loc)
        type = what(loc)
        thumbnail(loc, thumb)
        print(thumb_lDir+thumb)
        regImg(loc, orig_url, "./images/thumbnail/"+thumb, type)
    
if __name__ == '__main__' :
    main()
