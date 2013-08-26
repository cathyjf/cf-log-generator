# cf-log-generator

Created by [Cathy J. Fitzpatrick][1].

After deploying CloudFlare on a domain, web server access logs become
significantly less useful because many resources are served directly
from CloudFlare's cache and so bypass the web server entirely.

This program attempts to mitigate the issue somewhat by using the
CloudFlare API to generate basic access logs for a website which sits
behind the CloudFlare reverse proxy network.

*Note*: This program is under development and is not done yet.


[1]: https://cathyjf.com
