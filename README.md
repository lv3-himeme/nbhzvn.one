# Nobihaza Vietnam Community Collection

**Nobihaza Vietnam Community Collection** website ([nbhzvn.one](https://nbhzvn.one))'s official source code repository, used as a "game storage" website to upload and manage various Nobihaza games for ease of downloading, searching, and even linking to a chatbot.

This website uses PHP for backend, and MySQL for database management.

## Requirements

* A PHP and MySQL server, of course! PHP 8 or higher, and MySQL 10 or higher are required for the website to function properly.

* `mysqli`, `curl` PHP extensions, and `mod_rewrite` PHP module are also required.

* Set the `upload_max_filesize` and `post_max_size` PHP settings as high as possible to be able to upload large game files.

## How to install

1. Clone this repository.

2. Edit the `.env.sample` file to fit your server configuration. After that, rename the file to `.env`.

3. Upload the entire folder to your server's root `public_html`/`htdocs` folder and you're done! The database will also be setup automatically.

## Features to-do list

[ ] Games management
[X] Accounts management
[ ] Game following/rating/commenting
[ ] Advanced search
[ ] Discord webhook integration
[ ] Games API documentation for chatbots/applications

## Contributing

As an open-source repository, any contributions are greatly appreciated!

1. Fork the project

2. Create a new branch and edit the source code as your liking

3. Commit the changes and push them to the created branch

4. Create a pull request and wait for me to review and merge it!

## License

### Back-end PHP source code

All back-end PHP source code (excluding the HTML, CSS and JavaScript code) are licensed under the MIT License.

The back-end PHP source code also makes use of the following third-party libraries without any modifications:

- `vlucas/phpdotenv` licensed under [BSD-3-Clause license](https://github.com/vlucas/phpdotenv/blob/master/LICENSE).
- `phpmailer/phpmailer` licensed under [LGPL-2.1 license](https://github.com/PHPMailer/PHPMailer/blob/master/LICENSE).
- `soundasleep/html2text` licensed under [MIT license](https://github.com/soundasleep/html2text/blob/master/LICENSE.md).
- `erusev/parsedown` licensed under [MIT license](https://github.com/erusev/parsedown/blob/master/LICENSE.txt).

Therefore, you should see their respective licenses for details.

### Front-end template

This project also uses [a front-end template](https://colorlib.com/wp/template/anime) provided by [**Colorlib**](https://colorlib.com), licensed under the Creative Commons Attribution 3.0 (CC BY 3.0) license. You can find more information about their license at: https://colorlib.com/wp/licence

## Contact

You can contact me at: https://s1432.org/contact