# ssh.pub

This is a tool for managing public ssh keys. To make it easier to store, manage and install public ssh keys.

This idea came from [Jeff Lindsay](github.com/progrium) and his [Keychain.io](github.com/progrium/keychain.io) Project. It ended a few years ago and i wanted to use it so i decided to rebuild it.

In honor of the original project this will remain opensource. There will be more features to come but here's the basic usage.

Bash
----
Upload your default SSH key:

    curl -s https://ssh.pub/key/<email>/upload | bash

Install your key into authorized_keys:

    curl -s https://ssh.pub/key/<email>/install | bash

Upload your other named key SSH key:

    curl -s https://ssh.pub/key/<email>/<namedkey>/upload?keypath=/path/to/key.pub | bash

Install your named key into authorized_keys:

    curl -s https://ssh.pub/key/<email>/<namedkey>/install | bash



URLS
----
    https://ssh.pub/key/<email>
    https://ssh.pub/key/<email>/upload
    https://ssh.pub/key/<email>/install
    https://ssh.pub/key/<email>/fingerprint
    https://ssh.pub/key/<email>/confirm/<token>
    https://ssh.pub/key/<email>/all
    https://ssh.pub/key/<email>/all/install
    https://ssh.pub/key/<email>/<namedkey>
    https://ssh.pub/key/<email>/<namedkey>/fingerprint
    https://ssh.pub/key/<email>/<namedkey>/install
    https://ssh.pub/key/<email>/<namedkey>/upload

