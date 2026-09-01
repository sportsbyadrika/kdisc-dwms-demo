<?php
/** Wraps a dashboard page body in the sidebar shell. */
partial('dash-shell', ['slot' => $slot, 'nav' => $nav, 'identity' => $identity]);
