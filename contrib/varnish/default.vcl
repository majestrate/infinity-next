#
# save this file to /etc/varnish/default.vcl
#

vcl 4.0;

backend default {
        # from nginx config
        .host = "127.0.0.1";
        .port = "8080";
        .probe = {
                .interval = 5s;
                .timeout = 250ms;
                .window = 3;
                .threshold = 2;
        }
        
}

# only allow localhost to purge the cache
acl purge {
        "localhost";
}

sub vcl_recv {

        # set x-forwarded for header
        set req.http.X-Forwarded-For = client.ip;
        
        # don't cache images
        if (req.url ~ "\.(png|jpg|gif|jpeg|webm)$") {
                return (pass);
        }
        
        # allow purge from only localhost
        if (req.method == "PURGE") {
                if (!client.ip ~ purge) {
                        return(synth(405, "Not allowed."));
                }
                return (purge);
        }

        # don't cache post
        if (req.method == "POST") {
                return (pass);
        }
        if (req.url ~ thread) {
                return(hash);
        }
}

# store in cache only by url, not backend host
sub vcl_hash {
        hash_data(req.url);
        return (lookup);
}

sub vcl_deliver {
        return (deliver);
}

sub vcl_miss {
        return (fetch);
}

sub vcl_hit {
        return (deliver);
}