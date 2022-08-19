# October CMS w/ Docker

## Development
Run using the `docker-compose.yml` which uses Dockerfile and other config from the .docker folder.  `docker-compose.yml` contains config for development and references development files in `.docker/dev`. The primary `./Dockerfile` is used to build for production.  This is very similar to `./.docker/dev/Dockerfile`, the main difference being XDebug is not included in production.

We like to use SSL certs in development to run as close to production as possible.  We're used to using Laravel Valet, so we create certificates in Valet then copy them across.  5 minute manual setup at the beginning of the project but it works really well.

## Templates
Because Cloud Run is stateless, all services need to be decoupled, this includes /themes/content and /themes/meta - if using Static Pages.
Luckily October CMS already has a feature for this.  In .env.docker you will see DATABASE_TEMPLATES, which should also be picked up in /config/cms.php.  Setting to true will store all content in the database.

Just note that in Dev, combined with XDebug, it can get pretty slow, production is fine though.

## Assets/media
We use '\Superbalist\LaravelGoogleCloudStorage\GoogleCloudStorageServiceProvider' as you can see in config/app.php.
You can follow the docs and get an auth file for dev and self auth in production, however, because we split cloud run and storage across projects in Google, we have all the auth info in my .env file - ref all the GOOGLE_CLOUD_ entries

Add GCS to your config/filesystems.php as in this repo

We also use cloud CDN to access all assets, that's where the .env GOOGLE_CLOUD_STORAGE_API_URI comes in.

## Laravel Passport
We've built a plugin to incorporate full Passport OAuth2 functionality into October CMS as if it were laravel.  We won't include into a public repo, but contact us for info if needed.

## Tests
Testing is very similar to Laravel.

We have included example setup in the Demo plugin.

`BaseTestCase.php` may not be needed in all cases.  Laravel Factories are not included in October CMS by default, so they are registered here.

The setup method can also be used to register other plugins or bind instances to the container for testing.

## Deploying
We deploy through GitHub and have actions there to run tests. See `.github/workflows`.  We then build an image with Google Cloud Build, which is triggered by a push to master in our repo.  ref cloudbuild.yaml

This build config does a few things, first, it gets our production .env file which we store in Secrets Manager.  Then builds the production container from ./Dockerfile.  We then push and store the image in Artifact Registry.  Finally, we deploy a new Cloud Run instance from that image.

## Build
We run Laravel Mix in our custom theme, so you will see in the Dockerfile that it does a full composer install in the root folder, then an npm install && run production in the themes directory.  If this doesn't suit your project then you will have to change it.

## Node
Currently JS apps are built using the standard Laravel Mix config within the theme.  The build command sits in the Dockerfile.  If you are looking to reduce container size these assets can also be compiled externally before the image is built.

For Cloud Build, the below can be used, noting that any Env files will need to be copied in prior just like the core `.env`
```
steps:
    ...

    # NPM install and run production
    - name: node:$_NODE_VERSION
      entrypoint: npm
      args: ['--prefix', './themes/swiss8', 'install']
    - name: node:$_NODE_VERSION
      entrypoint: npm
      args: ['--prefix', './themes/swiss8', 'rebuild', 'node-sass']
    - name: node:$_NODE_VERSION
      entrypoint: npm
      args: ['--prefix', './themes/swiss8', 'run', 'production']

    ...

substitutions:
    _NODE_VERSION: 14.17.5
```

## Production
We run Redis and SQL, which you will need to set up through a VPC Connector to connect your Cloud Run project to.
You will also have to make sure the service account that you use for Cloud Build and deploy to Cloud Run has all the necessary permissions - access secret manager, build images and push to artifact registry, deploy to cloud run.
