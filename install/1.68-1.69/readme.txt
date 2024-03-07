Changelog
---------
- Initial support for linking accounts with external authenatication providers

- Browser fingerprinting for better multi-detection

- Docker package included for easy dev environment setup, with config.sample.php changed
to work with the docker package with minimal modification.

- Composer added for PHP dependencies

- User relationships can be managed in the system by users, allowing
users to register outside relationships that may influence games or 
that would otherwise get confused with multi-account/meta-game detection.
User suspicions can also be managed using the same system.

- Reliability rating calculations optimized to allow lock-free calculations in realtime.
- Api locking improved

- New 1v1 variant added: ColdWar

- Added play-now mode allowing users without accounts to jump straight into a bot game

WARNING: config.php has a function added to detect whether the server is in play-now mode, so 
if you are updating from an old version with an old config.php you need to copy the new function
from config.sample.php
