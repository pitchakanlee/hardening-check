## Step to set up `hardening-check` for FreeBSD 14

1. Clone the `hardening-check` repository

```bash
git clone https://github.com/pitchakanlee/hardening-check.git
cd hardening-check
```

2. Move the `hardening-check` file to `/usr/local/bin/` directory

```bash
sudo mv freebsd14/hardening-check /usr/local/bin/
```

3. Create `hardening-check` directory in `/usr/local/etc/` directory to collect the profile

```bash
sudo mkdir /usr/local/etc/hardening-check
```

4. Move the `profile` file to `/usr/local/etc/hardening-check` directory

```bash
sudo mv -r freebsd14/profile /usr/local/etc/hardening-check
```

5. Run `hardening-check` command

```bash
hardening-check -h
```

<hr>

### Example of usage:

1. `help` command

```
$ hardening-check -h

```
