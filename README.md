# StudEzy Studying Platform

## About
StudEzy is a free Platform for providing learning materials to students, interacting with them and much more. For more information about StudEzy visit: https://studezy.com/about

DISCLAIMER: AT THE MOMENT THIS SOFTWARE IS STILL IN EARLY DEVELOPMENT. DO NOT USE THIS SOFTWARE IN PRODUCTION ENVIRONMENTS! However you might start to experiment and/or contribute to this project.

## Use
You can use StudEzy at any time using the publicly hosted instances at: https://studezy.com

However, you can also run and host your own version of StudEzy by following the installation guide below. This will provide you with greater independence over configuration and allow you to host your learning contents in a simple and comprehensive way by yourself.

## Installation
StudEzy is made up of several components, some of them are provided as docker images in their respective release section, while others will require you to manually configure them.

### Prerequisites
To run StudEzy, you need to have access to AWS in order to use an s3-bucket as storage for contents uploaded to the platform. Other methods of storage will hopefully soon also be supported.

Create two separate buckets, one for media assets and one for documents/chapters. Configure them for public access. Use a bucket policy like this:

  {
    "Version": "2012-10-17",
    "Statement": [
        {
            "Sid": "Statement1",
            "Effect": "Allow",
            "Principal": {
                "AWS": "*"
            },
            "Action": [
                "s3:GetObject",
                "s3:PutObject",
                "s3:DeleteObject"
            ],
            "Resource": "YOUR_BUCKET_ARN/*"
        }
    ]
}

NOTE: This will allow public read/write access to your bucket. As mentioned, do not use this version in production!

### Components
The following components are available as docker images in their respective repository's release section:
- The main platform (this repository)
- The chatserver (see: https://github.com/nfa04/studezy-chat)
- The docserver (see: https://https://github.com/nfa04/docs-studezy-s3)

The following components will also be required, but are not provided as docker images, however configuring them should be pretty straight-forward:
- A MySQL server
- An Apache Cassandra Server (we recommend you use DataStax for ease of use and as other services/providers might not yet work with this release)

To start right away, download and verify all docker images using the provided checksums. 

Continue by installing an instance of a MySQL Server, then configure it. This works different on different operating systems, so have a look at the official installation guide for your OS. I recommend you to go with a Linux distribution of your choice.

Now create a new database and import the database structure which can be found here: https://github.com/nfa04/studezy-studyplatform/blob/main/db_scheme.sql

All containers will later require you to provide them with the necessary credentials to your other components and some additional configuration. Please read the README.md file of the respective component for more details. For the configuration of the main component, which can be found in this repository, read the section below.

### Configuring your self-hosted instance's main component
The main component is shipped inside a docker container to make it easier for you to deploy your own instance. However you are still required to add some configuration to it which is necessary for it to connect to your other components.

Please make sure you have verified your download using the provided checksums first!

#### .studezy-server-vars.json
.studezy-server-vars.json is the main configuration file for this component. You can find an example file here: https://github.com/nfa04/studezy-studyplatform/blob/main/.studezy-server-vars.json

Please download this file and fill in your credentials.

You should then proceed by supplying it to your container:

sudo docker cp /path/to/your/file/on/host CONTAINER_ID:/var/www/.studezy-server-vars.json

#### Configuring sendmail
You should then proceed to configure your PHP-sendmail component. Please follow the official PHP-Manual for information on how to set this up.

#### Setting up other components
To set up the other required components, please read:
- https://github.com/nfa04/docs-studezy-s3/blob/main/README.md
- https://github.com/nfa04/studezy-chat/blob/main/README.md

#### Running the container
You can run this container like any other docker container. Make sure to bind port 80 to any port on your host machine you would like.

#### Checking your installation
Go to your browser and open the host:port you deployed your docker container to. You should see the landing page.

## Contact
For any issues related to this software please contact: info@studezy.com
