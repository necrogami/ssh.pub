# ssh.pub

This is a tool for managing public ssh keys. To make it easier to store, manage and install public ssh keys.

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

