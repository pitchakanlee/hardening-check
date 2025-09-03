## Step to set up `hardening-check` for Debian 12

> Note: running the script on FreeBSD 14 requires `PHP8.2` and `sudo`

1. Clone the `hardening-check` repository

```bash
git clone https://github.com/pitchakanlee/hardening-check.git
cd hardening-check/debian12
```

2. Move the `hardening-check` file to `/usr/local/bin/` directory

```bash
sudo mv hardening-check /usr/local/bin/
```

3. Change permission of `hardening-check`

```bash
sudo chmod 755 /usr/local/bin/hardening-check
```

4. Create `hardening-check` directory in `/usr/local/etc/` directory to collect the profile

```bash
sudo mkdir /usr/local/etc/hardening-check
```

5. Move the `profile` file to `/etc/hardening-check` directory

```bash
sudo mv profiles/ /etc/hardening-check/
```

6. Run `hardening-check` command

```bash
hardening-check -h
```

<hr>

### Example of usage:

1. `help` command

```
$ hardening-check -h
```

Output:

```
Hardening Check Tool v1.0.0

Usage:
  [sudo] hardening-check --profile <profile> [options]

Options:
  -p, --profile     Specify the profile to use (file or directory name).
  -s, --section     Specify sections to run from a profile directory.
  -a, --all         Run all sections in the profile directory (default).
  -o, --output      Display full, detailed audit output for each check.
  -c, --cron        Generate concise output without color, suitable for cronjobs.
  -h, --help        Show this help message.

Available Profiles:
  - cis_debian-12_v1-1-0
```

2. `profile` command

```
$ hardening-check -p cis_debian-12_v1-1-0
```

Output:

```
Starting Hardening Check
Profile: cis_debian-12_v1-1-0
Warning: Running in non-privileged mode. Some checks will be skipped.

--- Running Profile Module: cis_debian-12_v1-1-0_1.php ---

# [1] Initial Setup

## [1.1] Filesystem
### [1.1.1] Configure Filesystem Kernel Modules
[1.1.1.1] [PASS] Ensure cramfs kernel module is not available
[1.1.1.2] [PASS] Ensure freevxfs kernel module is not available
[1.1.1.3] [PASS] Ensure hfs kernel module is not available
[1.1.1.4] [PASS] Ensure hfsplus kernel module is not available
[1.1.1.5] [PASS] Ensure jffs2 kernel module is not available
[1.1.1.6] [PASS] Ensure overlayfs kernel module is not available
[1.1.1.7] [PASS] Ensure squashfs kernel module is not available
[1.1.1.8] [PASS] Ensure udf kernel module is not available
[1.1.1.9] [PASS] Ensure usb-storage kernel module is not available
[1.1.1.10] [PASS] Ensure unused filesystems kernel modules are not available
...
```

3. `profile` and `section` command

```
$ hardening-check -p cis_debian-12_v1-1-0 -s 6
```

Output:

```
Starting Hardening Check
Profile: cis_debian-12_v1-1-0
Warning: Running in non-privileged mode. Some checks will be skipped.

--- Running Profile Module: cis_debian-12_v1-1-0_6.php ---

# [6] Logging and Auditing

## [6.1] System Logging
### [6.1.1] Configure systemd-journald service
[6.1.1.1] [PASS] Ensure journald service is enabled and active
[6.1.1.2] [PASS] Ensure journald log file access is configured
[6.1.1.3] [FAIL] Ensure journald log file rotation is configured
[6.1.1.4] [PASS] Ensure only one logging system is in use
### [6.1.2] Ensure permissions on SSH private host key files are configured
#### [6.1.2.1] Configure systemd-journal-remote
[6.1.2.1.1] [FAIL] Ensure systemd-journal-remote is installed
[6.1.2.1.2] [FAIL] Ensure systemd-journal-upload authentication is configured
[6.1.2.1.3] [FAIL] Ensure systemd-journal-upload is enabled and active
[6.1.2.1.4] [PASS] Ensure systemd-journal-remote service is not in use
[6.1.2.2] [FAIL] Ensure journald ForwardToSyslog is disabled
[6.1.2.3] [FAIL] Ensure journald Compress is configured
[6.1.2.4] [FAIL] Ensure journald Storage is configured

...

================================================================================
Scan Summary
Total Checks: 52 Executed | Passed: 4 | Failed: 32 | Manual/Review: 16 | Error: 0 | Skipped: 10
================================================================================
```

#### Note :

- Using sudo before the hardening-check command is essential because it runs the script with root

<hr>

### Status Definitions:

- Passed: The check was performed and the configuration is compliant with the hardening benchmark.

- Failed: The check was performed and the configuration is not compliant with the hardening benchmark. Remediation is required.

- Manual: The check was performed, but the configuration requires manual verification. The correct setting depends on a specific site policy that cannot be automatically determined.

- Skipped: The check was not performed for one of the following reasons:

  - The check requires sudo privileges to run, but the script was run without them.
  - The check is not applicable to the current system configuration. For example, if UFW is the active firewall, checks related to nftables or iptables will be skipped.

- Error: The audit could not be completed because the audit_script itself contains a syntax error or could not be executed.
