#!/bin/bash
 
USERNAME=chatupon
PASSWORD=b840bf
FROM=0000
TO=0614965469
MESSAGE="Test send sms form hagtag na ja"
curl -q "http://www.thsms.com/api/rest?method=send&username=$USERNAME&password=$PASSWORD&from=$FROM&to=$TO&message=$MESSAGE"
