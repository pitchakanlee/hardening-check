## Step to set up `hardening-check` for FreeBSD 14

> Note: running the script on FreeBSD 14 requires `PHP8.2` and `sudo`

1. Clone the `hardening-check` repository

```bash
git clone https://github.com/pitchakanlee/hardening-check.git
cd hardening-check/freebsd14
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

5. Move the `profile` file to `/usr/local/etc/hardening-check` directory

```bash
sudo mv profiles/ /usr/local/etc/hardening-check/
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
  - cis_freebsd-14_v1-0-1
```

2. `profile` command

```
$ hardening-check -p cis_freebsd-14_v1-0-1
```

Output:

```
Starting Hardening Check
Profile: cis_freebsd-14_v1-0-1

--- Running Profile Module: cis_freebsd-14_v1-0-1_1.php ---

# [1] Initial Setup

## [1.1] Filesystem & Bootloader
### [1.1.1] Configure Filesystem Kernel Modules
[1.1.1.1] [PASS] Ensure ext2fs kernel module is not available
[1.1.1.2] [PASS] Ensure msdosfs kernel module is not available
[1.1.1.3] [PASS] Ensure zfs kernel module is not available
### [1.1.2] Configure Filesystem Partitions
#### [1.1.2.1] Configure /tmp
[1.1.2.1.1] [PASS] Ensure /tmp is a separate partition
[1.1.2.1.2] [PASS] Ensure nosuid option set on /tmp partition
[1.1.2.1.3] [PASS] Ensure noexec option set on /tmp partition
...
```

3. `profile` and `section` command

```
$ hardening-check -p cis_freebsd-14_v1-0-1 -s 6
```

Output:

```
Starting Hardening Check
Profile: cis_freebsd-14_v1-0-1

--- Running Profile Module: cis_freebsd-14_v1-0-1_6.php ---

# [6] System Maintenance

## [6.1] System File Permissions
[6.1.1] [PASS] Ensure permissions on /etc/passwd are configured
[6.1.2] [PASS] Ensure permissions on /etc/group are configured
[6.1.3] [PASS] Ensure permissions on /etc/master.passwd are configured
[6.1.4] [PASS] Ensure permissions on /etc/shells are configured
[6.1.5] [PASS] Ensure world writable files and directories are secured
[6.1.6] [PASS] Ensure no unowned or ungrouped files or directories exist
[6.1.7] [MANUAL] Ensure SUID and SGID files are reviewed

## [6.2] Local User and Group Settings
[6.2.1] [PASS] Ensure accounts in /etc/master.passwd use shadowed passwords
[6.2.2] [PASS] Ensure /etc/master.passwd password fields are not empty
[6.2.3] [PASS] Ensure all groups in /etc/passwd exist in /etc/group
[6.2.4] [PASS] Ensure no duplicate UIDs exist
[6.2.5] [PASS] Ensure no duplicate GIDs exist
[6.2.6] [PASS] Ensure no duplicate user names exist
[6.2.7] [PASS] Ensure no duplicate group names exist
[6.2.8] [FAIL] Ensure root path integrity
[6.2.9] [PASS] Ensure root is the only UID 0 account
[6.2.10] [PASS] Ensure local interactive user home directories are configured
[6.2.11] [MANUAL] Ensure local interactive user dot files access is configured

================================================================================
Scan Summary
Total Checks: 18 Executed | Passed: 16 | Failed: 0 | Manual: 2 | Error: 0 | Skipped: 0
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
