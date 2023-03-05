# Draft of ChatGPT clone with OpenAI API, Symfony UX and MongoDB

This is a demo application that uses the [Symfony UX](https://symfony.com/doc/current/ux.html) library to build a clone
of [ChatGPT](https://chatgpt.com/). We use the [OpenAI API](https://beta.openai.com/) to generate the responses and
[MongoDB](https://www.mongodb.com/) to store the conversations.

This is a weekend project, so don't expect too much. The code is not production-ready and the UX is not perfect. I would
like to improve it in the future to demonstrate the power of Symfony UX and Doctrine ODM.

If you want to try it, you need to create an account on [OpenAI](https://beta.openai.com/) and set the `OPENAI_API_KEY`.

**Contributions are welcome to improve the code and the UX.**

## Installation

Clone the repository and install the dependencies:

```console
$ git clone https://github.com/GromNaN/symfony-openai-ux.git
$ cd symfony-openai-ux
$ composer install 
```

Start the MongoDB server or create a [free MongoDB Atlas cluster](https://www.mongodb.com/developer/products/atlas/free-atlas-cluster/)
and set the `MONGODB_URI` environment variable in the `.env.local` file.

```console
$ docker-compose up -d
```

Initialize the database:

```console
$ php bin/console doctrine:mongodb:schema:create
```

Build the assets:

```console
$ npm install
$ npm run dev
```

Start the web server:

```console
$ symfony serve
```

License
-------

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
