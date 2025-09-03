<?php
// =============================================================
// == file: CIS_Debian_Linux_12_Benchmark_v1.1.0.pdf
// == section: 5
// =============================================================
return [
    // --- 5 Access Control ---
    [ 'id' => '5', 'title' => 'Access Control', 'type' => 'header' ],

    // --- 5.1 Configure SSH Server ---
    [ 'id' => '5.1', 'title' => 'Configure SSH Server', 'type' => 'header' ],


    // --- 5.1.1 Ensure permissions on /etc/ssh/sshd_config are configured ---
    [
        'id' => '5.1.1', 'title' => 'Ensure permissions on /etc/ssh/sshd_config are configured', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
   a_output=(); a_output2=()
   perm_mask='0177' && maxperm="$( printf '%o' $(( 0777 & ~$perm_mask)) )"
   f_sshd_files_chk()
   {
      while IFS=: read -r l_mode l_user l_group; do
         a_out2=()
         [ $(( $l_mode & $perm_mask )) -gt 0 ] && a_out2+=("    Is mode: \"$l_mode\"" \ "    should be mode: \"$maxperm\" or more restrictive")
         [ "$l_user" != "root" ] && a_out2+=("    Is owned by \"$l_user\" should be owned by \"root\"")
         [ "$l_group" != "root" ] && a_out2+=("    Is group owned by \"$l_user\" should be group owned by \"root\"")
         if [ "${#a_out2[@]}" -gt "0" ]; then
            a_output2+=("  - File: \"$l_file\":" "${a_out2[@]}")
         else
            a_output+=("  - File: \"$l_file\":" "    Correct: mode ($l_mode), owner ($l_user)" \ "    and group owner ($l_group) configured")
         fi
      done < <(stat -Lc '%#a:%U:%G' "$l_file")
   }
   [ -e "/etc/ssh/sshd_config" ] && l_file="/etc/ssh/sshd_config" && f_sshd_files_chk
   while IFS= read -r -d $'\0' l_file; do
      [ -e "$l_file" ] && f_sshd_files_chk
   done < <(find /etc/ssh/sshd_config.d -type f -name '*.conf' \( -perm /077 -o ! -user root -o ! -group root \) -print0 2>/dev/null)
   if [ "${#a_output2[@]}" -le 0 ]; then
      printf '%s\n' "" "- Audit Result:" "  ** PASS **" "${a_output[@]}" ""
   else
      printf '%s\n' "" "- Audit Result:" "  ** FAIL **" " - Reason(s) for audit failure:" "${a_output2[@]}"
      [ "${#a_output[@]}" -gt 0 ] && printf '%s\n' "" "- Correctly set:" "${a_output[@]}" ""
   fi
}
BASH
    ],

    // --- 5.1.2 Ensure permissions on SSH private host key files are configured ---
    [
        'id' => '5.1.2', 'title' => 'Ensure permissions on SSH private host key files are configured', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
   a_output=(); a_output2=()
   l_ssh_group_name="$(awk -F: '($1 ~ /^(ssh_keys|_?ssh)$/) {print $1}' /etc/group)"
   f_file_chk()
   {
      while IFS=: read -r l_file_mode l_file_owner l_file_group; do
         a_out2=()
         [ "$l_file_group" = "$l_ssh_group_name" ] && l_pmask="0137" || l_pmask="0177"
         l_maxperm="$( printf '%o' $(( 0777 & ~$l_pmask )) )"
         if [ $(( $l_file_mode & $l_pmask )) -gt 0 ]; then
            a_out2+=("    Mode: \"$l_file_mode\" should be mode: \"$l_maxperm\" or more restrictive")
         fi
         if [ "$l_file_owner" != "root" ]; then
            a_out2+=("    Owned by: \"$l_file_owner\" should be owned by \"root\"")
         fi
         if [[ ! "$l_file_group" =~ ($l_ssh_group_name|root) ]]; then
            a_out2+=("    Owned by group \"$l_file_group\" should be group owned by: \"$l_ssh_group_name\" or \"root\"")
         fi
         if [ "${#a_out2[@]}" -gt "0" ]; then
            a_output2+=("  - File: \"$l_file\"${a_out2[@]}")
         else
            a_output+=("  - File: \"$l_file\"" \ "    Correct: mode: \"$l_file_mode\", owner: \"$l_file_owner\" and group owner: \"$l_file_group\" configured")
         fi
      done < <(stat -Lc '%#a:%U:%G' "$l_file")
   }
   while IFS= read -r -d $'\0' l_file; do
      if ssh-keygen -lf &>/dev/null "$l_file"; then
         file "$l_file" | grep -Piq -- '\bopenssh\h+([^#\n\r]+\h+)?private\h+key\b' && f_file_chk
      fi
   done < <(find -L /etc/ssh -xdev -type f -print0 2>/dev/null)
   if [ "${#a_output2[@]}" -le 0 ]; then
      printf '%s\n' "" "- Audit Result:" "  ** PASS **" "${a_output[@]}" ""
   else
      printf '%s\n' "" "- Audit Result:" "  ** FAIL **" " - Reason(s) for audit failure:" "${a_output2[@]}"
      [ "${#a_output[@]}" -gt 0 ] && printf '%s\n' "" "- Correctly set:" "${a_output[@]}" ""
   fi
}
BASH
    ],

    // --- 5.1.3 Ensure permissions on SSH public host key files are configured ---
    [
        'id' => '5.1.3', 'title' => 'Ensure permissions on SSH public host key files are configured', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
   a_output=(); a_output2=()
   l_pmask="0133"; l_maxperm="$( printf '%o' $(( 0777 & ~$l_pmask )) )"
   f_file_chk()
   {
      while IFS=: read -r l_file_mode l_file_owner l_file_group; do
         a_out2=()
         if [ $(( $l_file_mode & $l_pmask )) -gt 0 ]; then
            a_out2+=("    Mode: \"$l_file_mode\" should be mode: \"$l_maxperm\" or more restrictive")
         fi
         if [ "$l_file_owner" != "root" ]; then
            a_out2+=("    Owned by: \"$l_file_owner\" should be owned by: \"root\"")
         fi
         if [ "$l_file_group" != "root" ]; then
            a_out2+=("    Owned by group \"$l_file_group\" should be group owned by group: \"root\"")
         fi
         if [ "${#a_out2[@]}" -gt "0" ]; then
            a_output2+=("  - File: \"$l_file\"" "${a_out2[@]}")
         else
            a_output+=("  - File: \"$l_file\"" \
            "    Correct: mode: \"$l_file_mode\", owner: \"$l_file_owner\" and group owner: \"$l_file_group\" configured")
         fi
      done < <(stat -Lc '%#a:%U:%G' "$l_file")
   }
   while IFS= read -r -d $'\0' l_file; do
      if ssh-keygen -lf &>/dev/null "$l_file"; then
         file "$l_file" | grep -Piq -- '\bopenssh\h+([^#\n\r]+\h+)?public\h+key\b' && f_file_chk
      fi
   done < <(find -L /etc/ssh -xdev -type f -print0 2>/dev/null)
   if [ "${#a_output2[@]}" -le 0 ]; then
      [ "${#a_output[@]}" -le 0 ] && a_output+=("  - No openSSH public keys found")
      printf '%s\n' "" "- Audit Result:" "  ** PASS **" "${a_output[@]}" ""
   else
      printf '%s\n' "" "- Audit Result:" "  ** FAIL **" " - Reason(s) for audit failure:" "${a_output2[@]}"
      [ "${#a_output[@]}" -gt 0 ] && printf '%s\n' "" "- Correctly set:" "${a_output[@]}" ""
   fi
}
BASH
    ],

    // --- 5.1.4 Ensure sshd access is configured ---
    [
        'id' => '5.1.4', 'title' => 'Ensure sshd access is configured', 'profile' => 'Level 1 - Server', 'type' => 'Automated',
	'requires_root' => true, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    a_output_info=()

    sshd_access_rules=$(sshd -T | grep -Pi -- '^\h*(allow|deny)(users|groups)\h+\H+')

    if [ -n "$sshd_access_rules" ]; then
        a_output_pass+=("  - At least one access control directive (Allow/Deny Users/Groups) is configured.")
        a_output_info+=("  - Please review the following configured rules against your site policy:")
        while IFS= read -r line; do
            a_output_info+=("    - $line")
        done <<< "$sshd_access_rules"
    else
        a_output_fail+=("  - No access control directives (AllowUsers, AllowGroups, DenyUsers, DenyGroups) are configured.")
    fi

    a_output_info+=("")
    a_output_info+=("  - Note: If 'Match' blocks are used, run a specific audit for those conditions.")
    a_output_info+=("    Example for user 'sshuser': # sshd -T -C user=sshuser")

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output_fail[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** MANUAL **"
        printf '%s\n' "${a_output_pass[@]}"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output_fail[@]}"

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "Edit /etc/ssh/sshd_config to include at least one of the following,"
        printf '%s\n' "according to your site policy:"
        printf '%s\n' "AllowUsers <userlist>"
        printf '%s\n' "AllowGroups <grouplist>"
        printf '%s\n' "DenyUsers <userlist>"
        printf '%s\n' "DenyGroups <grouplist>"
    fi
}
BASH
    ],

    // --- 5.1.5 Ensure sshd Banner is configured ---
    [
        'id' => '5.1.5', 'title' => 'Ensure sshd Banner is configured', 'profile' => 'Level 1 - Server', 'type' => 'Automated',
	'requires_root' => true, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    a_output_info=()

    banner_line=$(sshd -T | grep -Pi -- '^\h*banner\h+\S+')

    if [ -z "$banner_line" ]; then
        a_output_fail+=("  - The 'Banner' directive is not set in sshd_config.")
    else
        banner_file=$(echo "$banner_line" | awk '{print $2}')
        a_output_pass+=("  - The 'Banner' directive is set to '$banner_file'.")
        a_output_info+=("  - Banner file path: $banner_file")

        if [ ! -f "$banner_file" ]; then
            a_output_fail+=("  - The configured banner file '$banner_file' does not exist.")
        else
            a_output_info+=("  - Content of $banner_file for manual review:")
            a_output_info+=("  -------------------------------------------")
            while IFS= read -r line; do
                a_output_info+=("    $line")
            done < "$banner_file"
            a_output_info+=("  -------------------------------------------")

            OS_ID=$(grep '^ID=' /etc/os-release | cut -d= -f2 | sed -e 's/"//g')
            FORBIDDEN_PATTERN="(\\\\v|\\\\r|\\\\m|\\\\s|$OS_ID)"

            if grep -E -i "$FORBIDDEN_PATTERN" "$banner_file" >/dev/null 2>&1; then
                found_macros=$(grep -E -i -o "$FORBIDDEN_PATTERN" "$banner_file" | tr '\n' ' ')
                a_output_fail+=("  - Banner file '$banner_file' contains prohibited OS-specific information. Found: $found_macros")
            else
                a_output_pass+=("  - Banner file '$banner_file' does not contain prohibited information.")
                a_output_pass+=("  - Note: Please manually verify the banner content against your site policy.")
            fi
        fi
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output_fail[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "${a_output_pass[@]}"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output_fail[@]}"

        if [ "${#a_output_pass[@]}" -gt 0 ]; then
            printf '\n%s\n' "- Correctly set:"
            printf '%s\n' "${a_output_pass[@]}"
        fi

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "1. Edit /etc/ssh/sshd_config and set the 'Banner' parameter:"
        printf '%s\n' "   Banner /etc/issue.net"
        printf '%s\n' "2. Edit the banner file (/etc/issue.net) to contain only your site-policy-approved message."
        printf '%s\n' "   Example: # echo \"Authorized users only.\" > /etc/issue.net"
    fi
}
BASH
    ],

    // --- 5.1.6 Ensure sshd Ciphers are configured ---
    [
	'id' => '5.1.6', 'title' => 'Ensure sshd Ciphers are configured', 'profile' => 'Level 1 - Server', 'type' => 'Automated',
	'requires_root' => true, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    a_output_info=()

    sshd_ciphers=$(sshd -T | grep -i '^ciphers' | awk '{print $2}')

    if [ -z "$sshd_ciphers" ]; then
        a_output_pass+=("  - 'Ciphers' directive is not explicitly set, using strong defaults.")
    else
        a_output_info+=("  - Actively configured Ciphers: $sshd_ciphers")

        weak_ciphers=("3des-cbc" "aes128-cbc" "aes192-cbc" "aes256-cbc")
        found_weak="false"

        for cipher in "${weak_ciphers[@]}"; do
            if [[ "$sshd_ciphers" == *"$cipher"* ]]; then
                a_output_fail+=("  - Weak cipher '$cipher' is enabled.")
                found_weak="true"
            fi
        done

        if [ "$found_weak" == "false" ]; then
            a_output_pass+=("  - No weak ciphers (3des-cbc, aes128-cbc, aes192-cbc, aes256-cbc) are enabled.")
        fi

        if [[ "$sshd_ciphers" == *"chacha20-poly1305@openssh.com"* ]]; then
            a_output_info+=("  - Note: 'chacha20-poly1305@openssh.com' is enabled. Please review CVE-2023-48795 to ensure your system is patched.")
        fi
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output_fail[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "${a_output_pass[@]}"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output_fail[@]}"

        if [ "${#a_output_pass[@]}" -gt 0 ]; then
            printf '\n%s\n' "- Correctly set:"
            printf '%s\n' "${a_output_pass[@]}"
        fi

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "Edit /etc/ssh/sshd_config and add or modify the 'Ciphers' line."
        printf '%s\n' "To disable weak ciphers, prefix them with a minus sign (-)."
        printf '%s\n' "Example:"
        printf '%s\n' "Ciphers -3des-cbc,aes128-cbc,aes192-cbc,aes256-cbc"
    fi
}
BASH
    ],


    // --- 5.1.7 Ensure sshd ClientAliveInterval and ClientAliveCountMax are configured ---
    [
        'id' => '5.1.7', 'title' => 'Ensure sshd ClientAliveInterval and ClientAliveCountMax are configured', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => true, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    a_output_info=()

    sshd_config=$(sshd -T | grep -Pi -- '^\h*(clientaliveinterval|clientalivecountmax)\h')

    interval_line=$(echo "$sshd_config" | grep 'clientaliveinterval')
    if [ -n "$interval_line" ]; then
        interval_value=$(echo "$interval_line" | awk '{print $2}')
        a_output_info+=("  - Current ClientAliveInterval: $interval_value")
        if [ "$interval_value" -gt 0 ]; then
            a_output_pass+=("  - 'ClientAliveInterval' is configured and greater than 0.")
        else
            a_output_fail+=("  - 'ClientAliveInterval' is not configured to a value greater than 0.")
        fi
    else
        a_output_fail+=("  - 'ClientAliveInterval' is not configured.")
    fi

    count_line=$(echo "$sshd_config" | grep 'clientalivecountmax')
    if [ -n "$count_line" ]; then
        count_value=$(echo "$count_line" | awk '{print $2}')
        a_output_info+=("  - Current ClientAliveCountMax: $count_value")
        if [ "$count_value" -gt 0 ]; then
            a_output_pass+=("  - 'ClientAliveCountMax' is configured and greater than 0.")
        else
            a_output_fail+=("  - 'ClientAliveCountMax' is not configured to a value greater than 0.")
        fi
    else
        a_output_fail+=("  - 'ClientAliveCountMax' is not configured.")
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output_fail[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "${a_output_pass[@]}"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output_fail[@]}"

        if [ "${#a_output_pass[@]}" -gt 0 ]; then
            printf '\n%s\n' "- Correctly set:"
            printf '%s\n' "${a_output_pass[@]}"
        fi

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "Edit /etc/ssh/sshd_config and set ClientAliveInterval and ClientAliveCountMax."
        printf '%s\n' "Example:"
        printf '%s\n' "ClientAliveInterval 15"
        printf '%s\n' "ClientAliveCountMax 3"
    fi
}
BASH
    ],

    // --- 5.1.8 Ensure sshd DisableForwarding is enabled ---
    [
        'id' => '5.1.8', 'title' => 'Ensure sshd DisableForwarding is enabled', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => true, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    a_output_info=()
    PARAMETER="disableforwarding"
    EXPECTED_VALUE="yes"

    config_value=$(sshd -T | grep -i "^${PARAMETER} " | awk '{print $2}')

    if [ -n "$config_value" ]; then
        a_output_info+=("  - Current $PARAMETER setting: $config_value")

        if [ "$config_value" == "$EXPECTED_VALUE" ]; then
            a_output_pass+=("  - '$PARAMETER' is correctly set to '$EXPECTED_VALUE'.")
        else
            a_output_fail+=("  - '$PARAMETER' is incorrectly set to '$config_value'.")
        fi
    else
        a_output_fail+=("  - '$PARAMETER' is not configured.")
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output_fail[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "${a_output_pass[@]}"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output_fail[@]}"

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "Edit the /etc/ssh/sshd_config file to set the parameter:"
        printf '%s\n' "DisableForwarding yes"
    fi
}
BASH
    ],

    // --- 5.1.9 Ensure sshd GSSAPIAuthentication is disabled ---
    [
        'id' => '5.1.9', 'title' => 'Ensure sshd GSSAPIAuthentication is disabled', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => true, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    a_output_info=()
    PARAMETER="gssapiauthentication"
    EXPECTED_VALUE="no"

    config_value=$(sshd -T | grep -i "^${PARAMETER} " | awk '{print $2}')

    if [ -n "$config_value" ]; then
        a_output_info+=("  - Current $PARAMETER setting: $config_value")

        if [ "$config_value" == "$EXPECTED_VALUE" ]; then
            a_output_pass+=("  - '$PARAMETER' is correctly set to '$EXPECTED_VALUE'.")
        else
            a_output_fail+=("  - '$PARAMETER' is incorrectly set to '$config_value'.")
        fi
    else
        a_output_pass+=("  - '$PARAMETER' is not explicitly configured, defaulting to 'no'.")
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output_fail[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "${a_output_pass[@]}"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output_fail[@]}"

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "Edit the /etc/ssh/sshd_config file to set the parameter:"
        printf '%s\n' "GSSAPIAuthentication no"
    fi
}
BASH
    ],

    // --- 5.1.10 Ensure sshd HostbasedAuthentication is disabled ---
    [
        'id' => '5.1.10', 'title' => 'Ensure sshd HostbasedAuthentication is disabled', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => true, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    a_output_info=()
    PARAMETER="hostbasedauthentication"
    EXPECTED_VALUE="no"

    config_value=$(sshd -T | grep -i "^${PARAMETER} " | awk '{print $2}')

    if [ -n "$config_value" ]; then
        a_output_info+=("  - Current $PARAMETER setting: $config_value")

        if [ "$config_value" == "$EXPECTED_VALUE" ]; then
            a_output_pass+=("  - '$PARAMETER' is correctly set to '$EXPECTED_VALUE'.")
        else
            a_output_fail+=("  - '$PARAMETER' is incorrectly set to '$config_value'.")
        fi
    else
        a_output_pass+=("  - '$PARAMETER' is not explicitly configured, defaulting to 'no'.")
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output_fail[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "${a_output_pass[@]}"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output_fail[@]}"

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "Edit the /etc/ssh/sshd_config file to set the parameter:"
        printf '%s\n' "HostbasedAuthentication no"
    fi
}
BASH
    ],

    // --- 5.1.11 Ensure sshd IgnoreRhosts is enabled ---
    [
        'id' => '5.1.11', 'title' => 'Ensure sshd IgnoreRhosts is enabled', 'profile' => 'Level 1 - Server', 'type' => 'Automated',
	'requires_root' => true, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    a_output_info=()
    PARAMETER="ignorerhosts"
    EXPECTED_VALUE="no"

    config_value=$(sshd -T | grep -i "^${PARAMETER} " | awk '{print $2}')

    if [ -n "$config_value" ]; then
        a_output_info+=("  - Current $PARAMETER setting: $config_value")

        if [ "$config_value" == "$EXPECTED_VALUE" ]; then
            a_output_pass+=("  - '$PARAMETER' is correctly set to '$EXPECTED_VALUE'.")
        else
            a_output_fail+=("  - '$PARAMETER' is incorrectly set to '$config_value'.")
        fi
    else
        a_output_pass+=("  - '$PARAMETER' is not explicitly configured, defaulting to 'no'.")
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output_fail[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "${a_output_pass[@]}"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output_fail[@]}"

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "Edit the /etc/ssh/sshd_config file to set the parameter:"
        printf '%s\n' "HostbasedAuthentication no"
    fi
}
BASH
    ],

    // --- 5.1.12 Ensure sshd KexAlgorithms is configured  ---
    [
        'id' => '5.1.12', 'title' => 'Ensure sshd KexAlgorithms is configured', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => true, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    a_output_info=()

    sshd_kex_algos=$(sshd -T | grep -i '^kexalgorithms' | awk '{print $2}')

    if [ -z "$sshd_kex_algos" ]; then
        a_output_pass+=("  - 'KexAlgorithms' directive is not explicitly set, using strong defaults.")
    else
        a_output_info+=("  - Actively configured KexAlgorithms: $sshd_kex_algos")

        weak_algos=("diffie-hellman-group1-sha1" "diffie-hellman-group14-sha1" "diffie-hellman-group-exchange-sha1")
        found_weak="false"

        for algo in "${weak_algos[@]}"; do
            if [[ "$sshd_kex_algos" == *"$algo"* ]]; then
                a_output_fail+=("  - Weak Key Exchange algorithm '$algo' is enabled.")
                found_weak="true"
            fi
        done

        if [ "$found_weak" == "false" ]; then
            a_output_pass+=("  - No weak Key Exchange algorithms were found.")
        fi
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output_fail[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "${a_output_pass[@]}"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output_fail[@]}"

        if [ "${#a_output_pass[@]}" -gt 0 ]; then
            printf '\n%s\n' "- Correctly set:"
            printf '%s\n' "${a_output_pass[@]}"
        fi

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "Edit /etc/ssh/sshd_config and add or modify the 'KexAlgorithms' line."
        printf '%s\n' "To disable weak algorithms, prefix them with a minus sign (-)."
        printf '%s\n' "Example:"
        printf '%s\n' "KexAlgorithms -diffie-hellman-group1-sha1,diffie-hellman-group14-sha1"
    fi
}
BASH
    ],

    // --- 5.1.13 Ensure sshd LoginGraceTime is configured ---
    [
        'id' => '5.1.13', 'title' => 'Ensure sshd LoginGraceTime is configured', 'profile' => 'Level 1 - Server', 'type' => 'Automated',
	'requires_root' => true, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    a_output_info=()
    PARAMETER="logingracetime"

    config_value=$(sshd -T | grep -i "^${PARAMETER} " | awk '{print $2}')

    if [ -n "$config_value" ]; then
        a_output_info+=("  - Current $PARAMETER setting: $config_value seconds")

        if [ "$config_value" -ge 1 ] && [ "$config_value" -le 60 ]; then
            a_output_pass+=("  - '$PARAMETER' is correctly configured to be between 1 and 60 seconds.")
        else
            a_output_fail+=("  - '$PARAMETER' is '$config_value', which is not within the range of 1-60 seconds.")
        fi
    else
        a_output_fail+=("  - '$PARAMETER' is not configured.")
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output_fail[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "${a_output_pass[@]}"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output_fail[@]}"

        if [ "${#a_output_pass[@]}" -gt 0 ]; then
            printf '\n%s\n' "- Correctly set:"
            printf '%s\n' "${a_output_pass[@]}"
        fi

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "Edit the /etc/ssh/sshd_config file to set the LoginGraceTime parameter."
        printf '%s\n' "Example:"
        printf '%s\n' "LoginGraceTime 60"
    fi
}
BASH
    ],

    // --- 5.1.14 Ensure sshd LogLevel is configured ---
    [
        'id' => '5.1.14', 'title' => 'Ensure sshd LogLevel is configured', 'profile' => 'Level 1 - Server', 'type' => 'Automated',
	'requires_root' => true, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    a_output_info=()
    PARAMETER="loglevel"

    config_value=$(sshd -T | grep -i "^${PARAMETER} " | awk '{print $2}')

    if [ -n "$config_value" ]; then
        a_output_info+=("  - Current $PARAMETER setting: $config_value")

        if [ "$config_value" == "VERBOSE" ] || [ "$config_value" == "INFO" ]; then
            a_output_pass+=("  - '$PARAMETER' is correctly set to a compliant value ('$config_value').")
        else
            a_output_fail+=("  - '$PARAMETER' is set to '$config_value', which is not compliant. It should be VERBOSE or INFO.")
        fi
    else
        a_output_pass+=("  - '$PARAMETER' is not explicitly configured, defaulting to INFO.")
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output_fail[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "${a_output_pass[@]}"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output_fail[@]}"

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "Edit the /etc/ssh/sshd_config file to set the LogLevel parameter."
        printf '%s\n' "Example:"
        printf '%s\n' "LogLevel VERBOSE"
    fi
}
BASH
    ],

    // --- 5.1.15 Ensure sshd MACs are configured ---
    [
        'id' => '5.1.15', 'title' => 'Ensure sshd MACs are configured', 'profile' => 'Level 1 - Server', 'type' => 'Automated',
	'requires_root' => true, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    a_output_info=()

    sshd_macs=$(sshd -T | grep -i '^macs' | awk '{print $2}')

    if [ -z "$sshd_macs" ]; then
        a_output_pass+=("  - 'MACs' directive is not explicitly set, using strong defaults.")
    else
        a_output_info+=("  - Actively configured MACs: $sshd_macs")

        weak_macs=(
            "hmac-md5" "hmac-md5-96" "hmac-ripemd160" "hmac-sha1-96"
            "umac-64@openssh.com" "hmac-md5-etm@openssh.com"
            "hmac-md5-96-etm@openssh.com" "hmac-ripemd160-etm@openssh.com"
            "hmac-sha1-96-etm@openssh.com" "umac-64-etm@openssh.com"
            "umac-128-etm@openssh.com"
        )
        found_weak="false"

        for mac in "${weak_macs[@]}"; do
            if [[ "$sshd_macs" == *"$mac"* ]]; then
                a_output_fail+=("  - Weak MAC '$mac' is enabled.")
                found_weak="true"
            fi
        done

        if [ "$found_weak" == "false" ]; then
            a_output_pass+=("  - No weak MACs were found in the configuration.")
        fi
    fi

    a_output_info+=("  - Note: Review CVE-2023-48795 regarding the use of ETM (Encrypt-then-MAC) algorithms.")

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output_fail[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "${a_output_pass[@]}"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output_fail[@]}"

        if [ "${#a_output_pass[@]}" -gt 0 ]; then
            printf '\n%s\n' "- Correctly set:"
            printf '%s\n' "${a_output_pass[@]}"
        fi

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "Edit /etc/ssh/sshd_config and add or modify the 'MACs' line."
        printf '%s\n' "To disable weak MACs, prefix them with a minus sign (-)."
        printf '%s\n' "Example of a secure configuration:"
        printf '%s\n' "MACs -hmac-md5,hmac-md5-96,hmac-ripemd160,hmac-sha1-96,umac-64@openssh.com"
    fi
}
BASH
    ],

    // --- 5.1.16 Ensure sshd MaxAuthTries is configured ---
    [
        'id' => '5.1.16', 'title' => 'Ensure sshd MaxAuthTries is configured', 'profile' => 'Level 1 - Server', 'type' => 'Automated',
	'requires_root' => true, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    a_output_info=()
    PARAMETER="maxauthtries"
    RECOMMENDED_VALUE=4

    config_value=$(sshd -T | grep -i "^${PARAMETER} " | awk '{print $2}')

    if [ -n "$config_value" ]; then
        a_output_info+=("  - Current $PARAMETER setting: $config_value")

        if [ "$config_value" -le "$RECOMMENDED_VALUE" ]; then
            a_output_pass+=("  - '$PARAMETER' is correctly configured to be 4 or less.")
        else
            a_output_fail+=("  - '$PARAMETER' is '$config_value', which is greater than 4.")
        fi
    else
        a_output_fail+=("  - '$PARAMETER' is not configured, defaulting to 6.")
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output_fail[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "${a_output_pass[@]}"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output_fail[@]}"

        if [ "${#a_output_pass[@]}" -gt 0 ]; then
            printf '\n%s\n' "- Correctly set:"
            printf '%s\n' "${a_output_pass[@]}"
        fi

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "Edit the /etc/ssh/sshd_config file to set the MaxAuthTries parameter."
        printf '%s\n' "Example:"
        printf '%s\n' "MaxAuthTries 4"
    fi
}
BASH
    ],

    // --- 5.1.17 Ensure sshd MaxSessions is configured ---
    [
        'id' => '5.1.17', 'title' => 'Ensure sshd MaxSessions is configured', 'profile' => 'Level 1 - Server', 'type' => 'Automated',
	'requires_root' => true, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    a_output_info=()
    PARAMETER="maxsessions"
    RECOMMENDED_VALUE=10

    config_value=$(sshd -T | grep -i "^${PARAMETER} " | awk '{print $2}')
    
    if [ -n "$config_value" ]; then
        a_output_info+=("  - Current $PARAMETER setting: $config_value")
        
        if [ "$config_value" -le "$RECOMMENDED_VALUE" ]; then
            a_output_pass+=("  - '$PARAMETER' is correctly configured to be 10 or less.")
        else
            a_output_fail+=("  - '$PARAMETER' is '$config_value', which is greater than 10.")
        fi
    else
        a_output_pass+=("  - '$PARAMETER' is not explicitly configured, defaulting to 10.")
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output_fail[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "${a_output_pass[@]}"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output_fail[@]}"
        
        if [ "${#a_output_pass[@]}" -gt 0 ]; then
            printf '\n%s\n' "- Correctly set:"
            printf '%s\n' "${a_output_pass[@]}"
        fi

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "Edit the /etc/ssh/sshd_config file to set the MaxSessions parameter."
        printf '%s\n' "Example:"
        printf '%s\n' "MaxSessions 10"
    fi
}
BASH
    ],

    // --- 5.1.18 Ensure sshd MaxStartups is configured ---
    [
        'id' => '5.1.18', 'title' => 'Ensure sshd MaxStartups is configured', 'profile' => 'Level 1 - Server', 'type' => 'Automated',
	'requires_root' => true, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    a_output_info=()
    PARAMETER="maxstartups"
    
    config_value=$(sshd -T | grep -i "^${PARAMETER} " | awk '{print $2}')
    if [ -z "$config_value" ]; then
        config_value="10:30:100" 
        a_output_info+=("  - '$PARAMETER' is not explicitly configured, using default of '$config_value'.")
    else
        a_output_info+=("  - Current $PARAMETER setting: $config_value")
    fi

    start=$(echo "$config_value" | cut -d: -f1)
    rate=$(echo "$config_value" | cut -d: -f2)
    full=$(echo "$config_value" | cut -d: -f3)

    if [ "$start" -le 10 ]; then
        a_output_pass+=("  - 'start' value ('$start') is 10 or less.")
    else
        a_output_fail+=("  - 'start' value ('$start') is greater than 10.")
    fi

    if [ "$rate" -le 30 ]; then
        a_output_pass+=("  - 'rate' value ('$rate') is 30 or less.")
    else
        a_output_fail+=("  - 'rate' value ('$rate') is greater than 30.")
    fi

    if [ "$full" -le 60 ]; then
        a_output_pass+=("  - 'full' value ('$full') is 60 or less.")
    else
        a_output_fail+=("  - 'full' value ('$full') is greater than 60.")
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output_fail[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "${a_output_pass[@]}"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output_fail[@]}"
        
        if [ "${#a_output_pass[@]}" -gt 0 ]; then
            printf '\n%s\n' "- Correctly set:"
            printf '%s\n' "${a_output_pass[@]}"
        fi

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "Edit the /etc/ssh/sshd_config file to set the MaxStartups parameter."
        printf '%s\n' "Example:"
        printf '%s\n' "MaxStartups 10:30:60"
    fi
}
BASH
    ],

    // --- 5.1.19 Ensure sshd PermitEmptyPasswords is disabled ---
    [
        'id' => '5.1.19', 'title' => 'Ensure sshd PermitEmptyPasswords is disabled', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => true, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    a_output_info=()
    PARAMETER="permitemptypasswords"
    EXPECTED_VALUE="no"

    config_value=$(sshd -T | grep -i "^${PARAMETER} " | awk '{print $2}')
    
    if [ -n "$config_value" ]; then
        a_output_info+=("  - Current $PARAMETER setting: $config_value")
        
        if [ "$config_value" == "$EXPECTED_VALUE" ]; then
            a_output_pass+=("  - '$PARAMETER' is correctly set to '$EXPECTED_VALUE'.")
        else
            a_output_fail+=("  - '$PARAMETER' is incorrectly set to '$config_value'.")
        fi
    else
        a_output_pass+=("  - '$PARAMETER' is not explicitly configured, defaulting to 'no'.")
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output_fail[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "${a_output_pass[@]}"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output_fail[@]}"
        
        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "Edit the /etc/ssh/sshd_config file to set the parameter:"
        printf '%s\n' "PermitEmptyPasswords no"
    fi
}
BASH
    ],

    // --- 5.1.20 Ensure sshd PermitRootLogin is disabled ---
    [
        'id' => '5.1.20', 'title' => 'Ensure sshd PermitRootLogin is disabled', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => true, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    a_output_info=()
    PARAMETER="permitrootlogin"
    EXPECTED_VALUE="no"

    config_value=$(sshd -T | grep -i "^${PARAMETER} " | awk '{print $2}')
    
    if [ -n "$config_value" ]; then
        a_output_info+=("  - Current $PARAMETER setting: $config_value")
        
        if [ "$config_value" == "$EXPECTED_VALUE" ]; then
            a_output_pass+=("  - '$PARAMETER' is correctly set to '$EXPECTED_VALUE'.")
        else
            a_output_fail+=("  - '$PARAMETER' is incorrectly set to '$config_value'.")
        fi
    else
        a_output_fail+=("  - '$PARAMETER' is not explicitly configured and may default to a less secure setting.")
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output_fail[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "${a_output_pass[@]}"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output_fail[@]}"
        
        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "Edit the /etc/ssh/sshd_config file to set the parameter:"
        printf '%s\n' "PermitRootLogin no"
    fi
}
BASH
    ],

    // --- 5.1.21 Ensure sshd PermitUserEnvironment is disabled ---
    [
        'id' => '5.1.21', 'title' => 'Ensure sshd PermitUserEnvironment is disabled', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => true, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    a_output_info=()
    PARAMETER="permituserenvironment"
    EXPECTED_VALUE="no"

    config_value=$(sudo sshd -T | grep -i "^${PARAMETER} " | awk '{print $2}')
    
    if [ -n "$config_value" ]; then
        a_output_info+=("  - Current $PARAMETER setting: $config_value")
        
        if [ "$config_value" == "$EXPECTED_VALUE" ]; then
            a_output_pass+=("  - '$PARAMETER' is correctly set to '$EXPECTED_VALUE'.")
        else
            a_output_fail+=("  - '$PARAMETER' is incorrectly set to '$config_value'.")
        fi
    else
        a_output_pass+=("  - '$PARAMETER' is not explicitly configured, defaulting to 'no'.")
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output_fail[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "${a_output_pass[@]}"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output_fail[@]}"
        
        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "Edit the /etc/ssh/sshd_config file to set the parameter:"
        printf '%s\n' "PermitUserEnvironment no"
    fi
}
BASH
    ],

    // --- 5.1.22 Ensure sshd UsePAM is enabled ---
    [
        'id' => '5.1.22', 'title' => 'Ensure sshd UsePAM is enabled', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => true, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    a_output_info=()
    PARAMETER="usepam"
    EXPECTED_VALUE="yes"

    config_value=$(sshd -T | grep -i "^${PARAMETER} " | awk '{print $2}')
    
    if [ -n "$config_value" ]; then
        a_output_info+=("  - Current $PARAMETER setting: $config_value")
        
        if [ "$config_value" == "$EXPECTED_VALUE" ]; then
            a_output_pass+=("  - '$PARAMETER' is correctly set to '$EXPECTED_VALUE'.")
        else
            a_output_fail+=("  - '$PARAMETER' is incorrectly set to '$config_value'.")
        fi
    else
        a_output_fail+=("  - '$PARAMETER' is not explicitly configured and may not be active.")
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output_fail[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "${a_output_pass[@]}"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output_fail[@]}"
        
        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "Edit the /etc/ssh/sshd_config file to set the parameter:"
        printf '%s\n' "UsePAM yes"
    fi
}
BASH
    ],

    // --- 5.2 Configure privilege escalation ---
    [ 'id' => '5.2', 'title' => 'Configure privilege escalation', 'type' => 'header' ],

    // --- 5.2.1 Ensure sudo is installed ---
    [
	'id' => '5.2.1', 'title' => 'Ensure sudo is installed', 'profile' => 'Level 1 - Server', 'type' => 'Automated',
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    a_output_info=()

    if dpkg-query -s "sudo" &>/dev/null || dpkg-query -s "sudo-ldap" &>/dev/null; then
        if dpkg-query -s "sudo" &>/dev/null; then
            a_output_pass+=("  - Package 'sudo' is installed.")
        fi
        if dpkg-query -s "sudo-ldap" &>/dev/null; then
            a_output_pass+=("  - Package 'sudo-ldap' is installed.")
        fi
    else
        a_output_fail+=("  - Neither 'sudo' nor 'sudo-ldap' is installed.")
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output_fail[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "${a_output_pass[@]}"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output_fail[@]}"
        
        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "If LDAP functionality is not required, run:"
        printf '%s\n' "# sudo apt install sudo"
        printf '%s\n' ""
        printf '%s\n' "If LDAP functionality is required, run:"
        printf '%s\n' "# sudo apt install sudo-ldap"
    fi
}
BASH
    ],

    // --- 5.2.2 Ensure sudo commands use pty ---
    [
        'id' => '5.2.2', 'title' => 'Ensure sudo commands use pty', 'profile' => 'Level 1 - Server', 'type' => 'Automated',
	'requires_root' => true, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    a_output_info=()
    SUDOERS_FILES="/etc/sudoers /etc/sudoers.d/*"

    if grep -rPi -- '^\h*Defaults\h+([^#\n\r]+,\h*)?use_pty\b' $SUDOERS_FILES &>/dev/null; then
        a_output_pass+=("  - 'Defaults use_pty' is correctly set in a sudoers file.")
        found_config=$(grep -rPi -- '^\h*Defaults\h+([^#\n\r]+,\h*)?use_pty\b' $SUDOERS_FILES)
        a_output_info+=("  - Found setting in: $found_config")
    else
        a_output_fail+=("  - 'Defaults use_pty' is not set in any sudoers file.")
    fi

    if grep -rPi -- '^\h*Defaults\h+([^#\n\r]+,\h*)?!use_pty\b' $SUDOERS_FILES &>/dev/null; then
        a_output_fail+=("  - Found a 'Defaults !use_pty' setting, which disables the requirement.")
        found_override=$(grep -rPi -- '^\h*Defaults\h+([^#\n\r]+,\h*)?!use_pty\b' $SUDOERS_FILES)
        a_output_info+=("  - Found override in: $found_override")
    else
        a_output_pass+=("  - No 'Defaults !use_pty' override was found.")
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output_fail[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "${a_output_pass[@]}"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output_fail[@]}"
        
        if [ "${#a_output_pass[@]}" -gt 0 ]; then
            printf '\n%s\n' "- Correctly set:"
            printf '%s\n' "${a_output_pass[@]}"
        fi

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "Edit /etc/sudoers (using 'visudo') or a file in /etc/sudoers.d/ and add the following line:"
        printf '%s\n' "Defaults use_pty"
        printf '%s\n' "Also, ensure any line containing '!use_pty' is removed."
    fi
}
BASH
    ],

    // --- 5.2.3 Ensure sudo log file exists ---
    [
        'id' => '5.2.3', 'title' => 'Ensure sudo log file exists', 'profile' => 'Level 1 - Server', 'type' => 'Automated',
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    a_output_info=()
    SUDOERS_FILES="/etc/sudoers /etc/sudoers.d/*"

    logfile_config=$(grep -rPsi "^\h*Defaults\h+([^#]+,\h*)?logfile\h*=\h*(\"|\')?\S+(\"|\')?" $SUDOERS_FILES 2>/dev/null)

    if [ -n "$logfile_config" ]; then
        log_path=$(echo "$logfile_config" | grep -oP "logfile\s*=\s*\K\S+" | tr -d '"\"')
        a_output_pass+=("  - 'Defaults logfile' is configured.")
        a_output_info+=("  - Found setting in:")
        while IFS= read -r line; do
            a_output_info+=("    $line")
        done <<< "$logfile_config"

        if [ -f "$log_path" ]; then
            a_output_pass+=("  - The configured log file '$log_path' exists.")
        else
            a_output_fail+=("  - The configured log file '$log_path' does NOT exist.")
        fi
    else
        a_output_fail+=("  - 'Defaults logfile' is not set in any sudoers file.")
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output_fail[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "${a_output_pass[@]}"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output_fail[@]}"

        if [ "${#a_output_pass[@]}" -gt 0 ]; then
            printf '\n%s\n' "- Correctly set:"
            printf '%s\n' "${a_output_pass[@]}"
        fi

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "Edit /etc/sudoers (using 'visudo') or a file in /etc/sudoers.d/ and add the following line:"
        printf '%s\n' "Defaults logfile=\"/var/log/sudo.log\""
    fi
}
BASH
    ],

    // --- 5.2.4 Ensure users must provide password for privilege escalation ---
    [
        'id' => '5.2.4', 'title' => 'Ensure users must provide password for privilege escalation', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => true, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    a_output_info=()
    SUDOERS_FILES="/etc/sudoers /etc/sudoers.d/*"

    nopasswd_entries=$(grep -rPsi '^\h*[^#].*\bNOPASSWD:' $SUDOERS_FILES 2>/dev/null)

    if [ -n "$nopasswd_entries" ]; then
        a_output_fail+=("  - One or more sudoers entries contain a 'NOPASSWD' tag.")
        a_output_info+=("  - Found the following non-compliant entries:")
        while IFS= read -r line; do
            a_output_info+=("    $line")
        done <<< "$nopasswd_entries"
    else
        a_output_pass+=("  - No 'NOPASSWD' tags were found in any sudoers file.")
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output_fail[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "${a_output_pass[@]}"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output_fail[@]}"
        
        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "Edit the relevant sudoers file(s) using 'visudo' and remove any"
        printf '%s\n' "occurrences of the 'NOPASSWD' tag."
    fi
}
BASH
    ],

    // --- 5.2.5 Ensure re-authentication for privilege escalation is not disabled globally ---
    [
        'id' => '5.2.5', 'title' => 'Ensure re-authentication for privilege escalation is not disabled globally', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => true, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    a_output_info=()
    SUDOERS_FILES="/etc/sudoers /etc/sudoers.d/*"

    # --- Run the audit command to find any !authenticate tags ---
    authenticate_override=$(grep -rPsi '^\h*Defaults\h+.*!authenticate' $SUDOERS_FILES 2>/dev/null)

    if [ -n "$authenticate_override" ]; then
        a_output_fail+=("  - Found a 'Defaults !authenticate' setting, which globally disables re-authentication.")
        a_output_info+=("  - Found the following non-compliant entries:")
        while IFS= read -r line; do
            a_output_info+=("    $line")
        done <<< "$authenticate_override"
    else
        a_output_pass+=("  - No 'Defaults !authenticate' tags were found in any sudoers file.")
    fi

    # --- Display Results ---
    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output_fail[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "${a_output_pass[@]}"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output_fail[@]}"
        
        # --- Remediation Section ---
        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "Edit the relevant sudoers file(s) using 'visudo' and remove any"
        printf '%s\n' "occurrences of the '!authenticate' tag from any 'Defaults' line."
    fi
}
BASH
    ],

    // --- 5.2.6 Ensure sudo authentication timeout is configured correctly ---
    [
        'id' => '5.2.6', 'title' => 'Ensure sudo authentication timeout is configured correctly', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => true, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    a_output_info=()
    SUDOERS_FILES="/etc/sudoers /etc/sudoers.d/*"

    configured_timeout=$(sudo grep -rPsi "^\h*Defaults\h+.*timestamp_timeout\s*=" $SUDOERS_FILES 2>/dev/null | grep -oP 'timestamp_timeout=\K[0-9-]+' | tail -n 1)

    if [ -n "$configured_timeout" ]; then
        a_output_info+=("  - Found 'timestamp_timeout' explicitly set to: $configured_timeout minutes.")
        
        if [ "$configured_timeout" -ge 0 ] && [ "$configured_timeout" -le 15 ]; then
            a_output_pass+=("  - The configured timeout of '$configured_timeout' minutes is compliant (<= 15).")
        else
            a_output_fail+=("  - The configured timeout of '$configured_timeout' minutes is not compliant (should be between 0 and 15).")
        fi
    else
        default_timeout=$(sudo -V | awk -F': ' '/Authentication timestamp timeout/ {print $2}' | awk '{print $1}')
        
        if ! [[ "$default_timeout" =~ ^[0-9]+$ ]]; then
            a_output_fail+=("  - Could not determine default sudo timeout.")
        else
            a_output_info+=("  - 'timestamp_timeout' is not explicitly set. Using default: $default_timeout minutes.")
            if [ "$default_timeout" -le 15 ]; then
                a_output_pass+=("  - The default timeout of '$default_timeout' minutes is compliant (<= 15).")
            else
                a_output_fail+=("  - The default timeout of '$default_timeout' minutes is not compliant (should be <= 15).")
            fi
        fi
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output_fail[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "${a_output_pass[@]}"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output_fail[@]}"
        
        if [ "${#a_output_pass[@]}" -gt 0 ]; then
            printf '\n%s\n' "- Correctly set:"
            printf '%s\n' "${a_output_pass[@]}"
        fi

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "Edit /etc/sudoers (using 'visudo') and add or modify the 'timestamp_timeout' setting."
        printf '%s\n' "Example: Defaults timestamp_timeout=15"
    fi
}
BASH
    ],

    // --- 5.2.7 Ensure access to the su command is restricted ---
    [
        'id' => '5.2.7', 'title' => 'Ensure access to the su command is restricted', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    a_output_info=()
    l_su_pam_file="/etc/pam.d/su"

    pam_line=$(grep -Pi '^\h*auth\h+(required|requisite)\h+pam_wheel\.so\h+.*\buse_uid\b.*\bgroup=\S+\b' "$l_su_pam_file" 2>/dev/null)

    if [ -z "$pam_line" ]; then
        a_output_fail+=("  - Missing required line: auth required pam_wheel.so use_uid group=<groupname>")
    else
        a_output_info+=("  - PAM configuration found:")
        a_output_info+=("    $pam_line")

        group_name=$(echo "$pam_line" | grep -oP 'group=\K\S+')

        if [ -n "$group_name" ]; then
            group_entry=$(getent group "$group_name")
            group_users=$(echo "$group_entry" | awk -F: '{print $4}')

            if [ -z "$group_entry" ]; then
                a_output_fail+=("  - Group '$group_name' is not defined on the system.")
            elif [ -n "$group_users" ]; then
                a_output_fail+=("  - Group '$group_name' exists but contains users: $group_users")
            else
                a_output_pass+=("  - Group '$group_name' is properly configured and contains no users.")
            fi
        else
            a_output_fail+=("  - Could not extract group name from pam_wheel.so line.")
        fi
    fi

    [ "${#a_output_info[@]}" -gt 0 ] && {
        echo ""
        echo "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    }

    if [ "${#a_output_fail[@]}" -eq 0 ]; then
        echo ""
        echo "- Audit Result:"
        echo "  ** PASS **"
        printf '%s\n' "${a_output_pass[@]}"
    else
        echo ""
        echo "- Audit Result:"
        echo "  ** FAIL **"
        echo " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output_fail[@]}"

        echo ""
        echo "-- Suggestion --"
        echo "1. Create an empty group for controlling 'su' access (example: sugroup):"
        echo "     groupadd sugroup"
        echo "2. Edit /etc/pam.d/su and add the following line:"
        echo "     auth required pam_wheel.so use_uid group=sugroup"
        echo "3. Do not assign any users to this group unless explicitly authorized."
    fi

}
BASH
    ],

    // --- 5.3 Pluggable Authentication Modules ---
    [ 'id' => '5.3', 'title' => 'Pluggable Authentication Modules', 'type' => 'header' ],

    // --- 5.3.1 Configure PAM software packages ---
    [ 'id' => '5.3.1', 'title' => 'Configure PAM software packages', 'type' => 'header' ],    

    // --- 5.3.1.1 Ensure latest version of pam is installed ---
    [
        'id' => '5.3.1.1', 'title' => 'Ensure latest version of pam is installed', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    required_version="1.5.2-6"
    a_output_pass=()
    a_output_fail=()
    a_output_info=()

    pkg_info=$(dpkg-query -s libpam-runtime 2>/dev/null)

    if echo "$pkg_info" | grep -q '^Status: install ok installed'; then
        current_version=$(echo "$pkg_info" | grep '^Version:' | awk '{print $2}')
        a_output_info+=("  - libpam-runtime is installed.")
        a_output_info+=("  - Current version: $current_version")
        a_output_info+=("  - Required minimum version: $required_version")

        if dpkg --compare-versions "$current_version" ge "$required_version"; then
            a_output_pass+=("  - Version is up to date or newer.")
        else
            a_output_fail+=("  - Version is older than required.")
        fi
    else
        a_output_fail+=("  - libpam-runtime is not installed.")
    fi

    [ "${#a_output_info[@]}" -gt 0 ] && {
        echo ""
        echo "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    }

    if [ "${#a_output_fail[@]}" -eq 0 ]; then
        echo ""
        echo "- Audit Result:"
        echo "  ** PASS **"
        printf '%s\n' "${a_output_pass[@]}"
    else
        echo ""
        echo "- Audit Result:"
        echo "  ** FAIL **"
        echo " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output_fail[@]}"

        echo ""
        echo "-- Suggestion --"
        echo "To install or upgrade libpam-runtime to the latest version, run:"
        echo "  sudo apt update && sudo apt install --only-upgrade libpam-runtime"
    fi
}
BASH
    ],

    // --- 5.3.1.2 Ensure libpam-modules is installed ---
    [
        'id' => '5.3.1.2', 'title' => 'Ensure libpam-modules is installed', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{

    required_version="1.5.2-6"
    a_output_pass=()
    a_output_fail=()
    a_output_info=()

    pkg="libpam-modules"

    pkg_info=$(dpkg-query -s "$pkg" 2>/dev/null)

    if echo "$pkg_info" | grep -q '^Status: install ok installed'; then
        current_version=$(echo "$pkg_info" | grep '^Version:' | awk '{print $2}')
        a_output_info+=("  - $pkg is installed.")
        a_output_info+=("  - Current version: $current_version")
        a_output_info+=("  - Required minimum version: $required_version")

        if dpkg --compare-versions "$current_version" ge "$required_version"; then
            a_output_pass+=("  - Version is up to date or newer.")
        else
            a_output_fail+=("  - Version is older than required.")
        fi
    else
        a_output_fail+=("  - $pkg is not installed.")
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        echo ""
        echo "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output_fail[@]}" -eq 0 ]; then
        echo ""
        echo "- Audit Result:"
        echo "  ** PASS **"
        printf '%s\n' "${a_output_pass[@]}"
    else
        echo ""
        echo "- Audit Result:"
        echo "  ** FAIL **"
        echo " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output_fail[@]}"
        echo ""
        echo "-- Suggestion --"
        echo "Run the following command to install or upgrade $pkg:"
        echo "  sudo apt update && sudo apt install --only-upgrade $pkg"
    fi
}
BASH
    ],

    // --- 5.3.1.3 Ensure libpam-pwquality is installed ---
    [
        'id' => '5.3.1.3', 'title' => 'Ensure libpam-pwquality is installed', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    pkg="libpam-pwquality"
    a_output_pass=()
    a_output_fail=()
    a_output_info=()

    pkg_info=$(dpkg-query -s "$pkg" 2>/dev/null)

    if echo "$pkg_info" | grep -q '^Status: install ok installed'; then
        version=$(echo "$pkg_info" | grep '^Version:' | awk '{print $2}')
        a_output_info+=("  - $pkg is installed.")
        a_output_info+=("  - Version: $version")
        a_output_pass+=("  - Package $pkg is installed.")
    else
        a_output_fail+=("  - Package $pkg is NOT installed.")
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        echo ""
        echo "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output_fail[@]}" -eq 0 ]; then
        echo ""
        echo "- Audit Result:"
        echo "  ** PASS **"
        printf '%s\n' "${a_output_pass[@]}"
    else
        echo ""
        echo "- Audit Result:"
        echo "  ** FAIL **"
        echo " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output_fail[@]}"
        echo ""
        echo "-- Suggestion --"
        echo "Run the following command to install $pkg:"
        echo "  sudo apt update && sudo apt install $pkg"
    fi
}
BASH
    ],

    // --- 5.3.2 Configure pam-auth-update profiles ---
    [ 'id' => '5.3.2', 'title' => 'Configure pam-auth-update profiles', 'type' => 'header' ],    

    // --- 5.3.2.1 Ensure pam_unix module is enabled ---
    [
        'id' => '5.3.2.1', 'title' => 'Ensure pam_unix module is enabled', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

pam_files=(
    "/etc/pam.d/common-account"
    "/etc/pam.d/common-auth"
    "/etc/pam.d/common-password"
    "/etc/pam.d/common-session"
    "/etc/pam.d/common-session-noninteractive"
)

a_output_pass=()
a_output_fail=()
a_output_info=()

for file in "${pam_files[@]}"; do
    if [ ! -f "$file" ]; then
        a_output_fail+=("  - File $file does not exist.")
        continue
    fi

    # ตรวจสอบว่ามี pam_unix.so ในไฟล์นี้หรือไม่
    if grep -qP '\bpam_unix\.so\b' "$file"; then
        a_output_pass+=("  - pam_unix.so found in $file")
    else
        a_output_fail+=("  - pam_unix.so NOT found in $file")
    fi
done

# แสดงผล
if [ "${#a_output_fail[@]}" -eq 0 ]; then
    echo ""
    echo "- Audit Result:"
    echo "  ** PASS **"
    printf '%s\n' "${a_output_pass[@]}"
else
    echo ""
    echo "- Audit Result:"
    echo "  ** FAIL **"
    echo " - Reason(s) for audit failure:"
    printf '%s\n' "${a_output_fail[@]}"
    echo ""
    echo "-- Suggestion --"
    echo "Run the following command to enable pam_unix module:"
    echo "  sudo pam-auth-update --enable unix"
    echo ""
    echo "Note: If you use a custom PAM profile including pam_faillock, enable that module accordingly."
fi
BASH
    ],

    // --- 5.3.2.2 Ensure pam_faillock module is enabled ---
    [
        'id' => '5.3.2.2', 'title' => 'Ensure pam_faillock module is enabled', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    config_file="/usr/share/pam-configs/faillock"
    declare -a expected_content=(
    "Name: Enable pam_faillock to deny access"
    "Default: yes"
    "Priority: 0"
    "Auth-Type: Primary"
    "Auth:"
    "        [default=die]                   pam_faillock.so authfail"
    )

    a_output_pass=()
    a_output_fail=()

    if [ -f "$config_file" ]; then
        mapfile -t existing_content < "$config_file"
        if [ "${#existing_content[@]}" -eq "${#expected_content[@]}" ]; then
            mismatch=0
            for i in "${!expected_content[@]}"; do
                if [ "${existing_content[$i]}" != "${expected_content[$i]}" ]; then
                    mismatch=1
                    break
                fi
            done
            if [ "$mismatch" -eq 0 ]; then
                a_output_pass+=("Config file $config_file exists and content matches expected.")
            else
                a_output_fail+=("Config file $config_file exists but content differs from expected.")
            fi
        else
            a_output_fail+=("Config file $config_file exists but number of lines differ from expected.")
        fi
    else
        a_output_fail+=("Config file $config_file does not exist.")
    fi

    if [ "${#a_output_fail[@]}" -eq 0 ]; then
        echo ""
        echo "- Audit Result:"
        echo "  ** PASS **"
        printf '%s\n' "${a_output_pass[@]}"
    else
        echo ""
        echo "- Audit Result:"
        echo "  ** FAIL **"
        echo " - Reason(s):"
        printf '%s\n' "${a_output_fail[@]}"
        echo ""
        echo "-- Suggestion --"
        echo "To create or fix the pam_faillock profile, run this script or manually create the file with:"
        echo ""
        printf '%s\n' "${expected_content[@]}"
        echo ""
        echo "You can create the file using this script:"
        echo "sudo bash $0 --fix"
    fi

    if [ "$1" == "--fix" ]; then
        echo "Writing config file to $config_file ..."
        sudo mkdir -p "$(dirname "$config_file")"
        printf '%s\n' "${expected_content[@]}" | sudo tee "$config_file" > /dev/null
        echo "Done."
    fi
}
BASH
    ],

    // --- 5.3.2.3 Ensure pam_pwquality module is enabled ---
    [
        'id' => '5.3.2.3', 'title' => 'Ensure pam_pwquality module is enabled', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    pam_file="/etc/pam.d/common-password"
    a_output_pass=()
    a_output_fail=()

    if [ ! -f "$pam_file" ]; then
        a_output_fail+=("  - File $pam_file does not exist.")
    else
        if grep -Pq '\bpam_pwquality\.so\b' "$pam_file"; then
            a_output_pass+=("  - pam_pwquality.so module is enabled in $pam_file.")
        else
            a_output_fail+=("  - pam_pwquality.so module is NOT enabled in $pam_file.")
        fi
    fi

    if [ "${#a_output_fail[@]}" -eq 0 ]; then
        echo ""
        echo "- Audit Result:"
        echo "  ** PASS **"
        printf '%s\n' "${a_output_pass[@]}"
    else
        echo ""
        echo "- Audit Result:"
        echo "  ** FAIL **"
        echo " - Reason(s):"
        printf '%s\n' "${a_output_fail[@]}"
        echo ""
        echo "-- Suggestion --"
        echo "To enable pam_pwquality module, add or uncomment a line like this in $pam_file:"
        echo "  password requisite pam_pwquality.so retry=3"
        echo ""
        echo "You can enable it by running:"
        echo "  sudo pam-auth-update --enable pwquality"
    fi
}
BASH
    ],

    // --- 5.3.2.4 Ensure pam_pwhistory module is enabled ---
    [
        'id' => '5.3.2.4', 'title' => 'Ensure pam_pwhistory module is enabled', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    pam_file="/etc/pam.d/common-password"
    a_output_pass=()
    a_output_fail=()

    if [ ! -f "$pam_file" ]; then
        a_output_fail+=("  - File $pam_file does not exist.")
    else
        if grep -Pq '\bpam_pwhistory\.so\b' "$pam_file"; then
            a_output_pass+=("  - pam_pwhistory.so module is enabled in $pam_file.")
        else
            a_output_fail+=("  - pam_pwhistory.so module is NOT enabled in $pam_file.")
        fi
    fi

    if [ "${#a_output_fail[@]}" -eq 0 ]; then
        echo ""
        echo "- Audit Result:"
        echo "  ** PASS **"
        printf '%s\n' "${a_output_pass[@]}"
    else
        echo ""
        echo "- Audit Result:"
        echo "  ** FAIL **"
        echo " - Reason(s):"
        printf '%s\n' "${a_output_fail[@]}"
        echo ""
        echo "-- Suggestion --"
        echo "To enable pam_pwhistory module, add or uncomment a line like this in $pam_file:"
        echo "  password requisite pam_pwhistory.so remember=24 enforce_for_root try_first_pass use_authtok"
        echo ""
        echo "You can enable it by running:"
        echo "  sudo pam-auth-update --enable pwhistory"
    fi
}
BASH
    ],

    // --- 5.3.3 Configure PAM Arguments ---
    [ 'id' => '5.3.3', 'title' => 'Configure PAM Arguments', 'type' => 'header' ],

    // --- 5.3.3.1 Configure pam_faillock module ---
    [ 'id' => '5.3.3.1', 'title' => 'Configure pam_faillock module', 'type' => 'header'],

    // --- 5.3.3.1.1 Ensure password failed attempts lockout is configured ---
    [
        'id' => '5.3.3.1.1', 'title' => 'Ensure password failed attempts lockout is configured', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  FAILLOCK_CONF_FILE="/etc/security/faillock.conf"
  PAM_COMMON_AUTH_FILE="/etc/pam.d/common-auth"

  a_output_pass=()
  a_output_fail=()
  a_output_info=()
  a_output_suggestion=()
  AUDIT_OVERALL_STATUS="PASS"

  a_output_info+=("Checking for 'deny' setting in '$FAILLOCK_CONF_FILE' (should be 1-5).")
  # Audit 1: Verify deny in /etc/security/faillock.conf is no greater than 5
  if [ ! -f "$FAILLOCK_CONF_FILE" ]; then
    a_output_fail+=(" - Configuration file '$FAILLOCK_CONF_FILE' does not exist.")
    a_output_info+=("  - File: '$FAILLOCK_CONF_FILE' - Not found. This audit check cannot be performed fully.")
    AUDIT_OVERALL_STATUS="FAIL"
  else
    # Check if 'deny' is configured within the [1-5] range
    if grep -Piq '^\h*deny\h*=\h*[1-5]\b' "$FAILLOCK_CONF_FILE"; then
      FOUND_DENY=$(grep -Pi -- '^\h*deny\h*=\h*[1-5]\b' "$FAILLOCK_CONF_FILE")
      a_output_pass+=(" - '$FAILLOCK_CONF_FILE' 'deny' setting is correctly configured: '$FOUND_DENY'.")
      a_output_info+=("  - '$FAILLOCK_CONF_FILE' 'deny' setting found: '$FOUND_DENY'. This is within the recommended range.")
    else
      # Check if 'deny' is configured but outside the [1-5] range
      EXISTING_DENY=$(grep -Pi -- '^\h*deny\h*=\h*\d+\b' "$FAILLOCK_CONF_FILE" | awk -F'=' '{print $2}' | tr -d '[:space:]')
      if [[ -n "$EXISTING_DENY" ]]; then
        a_output_fail+=(" - '$FAILLOCK_CONF_FILE' 'deny' setting is '$EXISTING_DENY', which is greater than 5 or 0. Should be between 1-5.")
        a_output_info+=("  - '$FAILLOCK_CONF_FILE' 'deny' setting found: '$EXISTING_DENY'. This is outside the recommended range.")
        AUDIT_OVERALL_STATUS="FAIL"
        a_output_suggestion+=("Edit '$FAILLOCK_CONF_FILE' and set 'deny' to a value between 1 and 5 (e.g., 'deny = 5').")
      else
        a_output_fail+=(" - '$FAILLOCK_CONF_FILE' does not explicitly contain a 'deny' setting within the recommended range [1-5].")
        a_output_info+=("  - '$FAILLOCK_CONF_FILE' 'deny' setting not found or is commented out. It should be explicitly set.")
        AUDIT_OVERALL_STATUS="FAIL"
        a_output_suggestion+=("Add or uncomment the 'deny' setting in '$FAILLOCK_CONF_FILE' and set it to a value between 1 and 5 (e.g., 'deny = 5').")
      fi
    fi
  fi

  a_output_info+=("Checking for 'deny' argument in '$PAM_COMMON_AUTH_FILE' (should NOT be 0 or >5).")
  # Audit 2: Verify that the deny argument has not been set, or 5 or less in /etc/pam.d/common-auth
  if [ ! -f "$PAM_COMMON_AUTH_FILE" ]; then
    a_output_fail+=(" - PAM common-auth file '$PAM_COMMON_AUTH_FILE' does not exist.")
    a_output_info+=("  - File: '$PAM_COMMON_AUTH_FILE' - Not found. This audit check cannot be performed fully.")
    AUDIT_OVERALL_STATUS="FAIL"
  else
    if grep -Piq '^\h*auth\h+(?:requisite|required|sufficient)\h+pam_faillock\.so\h+([^#\n\r]+\h+)?deny\h*=\h*(0|[6-9]|[1-9][0-9]+)\b' "$PAM_COMMON_AUTH_FILE"; then
      FOUND_PAM_AUTH_DENY=$(grep -Pi -- '^\h*auth\h+(requisite|required|sufficient)\h+pam_faillock\.so\h+([^#\n\r]+\h+)?deny\h*=\h*(0|[6-9]|[1-9][0-9]+)\b' "$PAM_COMMON_AUTH_FILE")
      a_output_fail+=(" - '$PAM_COMMON_AUTH_FILE' contains 'deny' argument set to an unauthorized value (0 or >5): '$FOUND_PAM_AUTH_DENY'.")
      a_output_info+=("  - '$PAM_COMMON_AUTH_FILE' 'deny' argument found: '$FOUND_PAM_AUTH_DENY'. This is an unauthorized override.")
      AUDIT_OVERALL_STATUS="FAIL"
      a_output_suggestion+=("Remove or modify the 'deny' argument from the 'pam_faillock.so' line(s) in '$PAM_COMMON_AUTH_FILE' if it sets an unauthorized value (0 or >5).")
    else
      a_output_pass+=(" - '$PAM_COMMON_AUTH_FILE' 'deny' argument in pam_faillock.so is not set to an unauthorized value (0 or >5).")
      a_output_info+=("  - '$PAM_COMMON_AUTH_FILE' 'deny' argument in pam_faillock.so is not overriding to an unauthorized value.")
    fi
  fi

  echo ""
  echo "-- INFO --"
  printf '%s\n' "${a_output_info[@]}"

  echo ""
  echo "- Audit Result:"
  if [ "$AUDIT_OVERALL_STATUS" == "PASS" ]; then
    echo "  ** PASS **"
  else
    echo "  ** FAIL **"
  fi

  if [ "${#a_output_fail[@]}" -gt 0 ]; then
    echo "  - Reason(s) for audit failure:"
    printf '%s\n' "${a_output_fail[@]}"
  fi

  if [ "${#a_output_pass[@]}" -gt 0 ]; then
    echo ""
    echo "  - Correctly set:"
    printf '%s\n' "${a_output_pass[@]}"
  fi

  if [ "${#a_output_suggestion[@]}" -gt 0 ]; then
    echo ""
    echo "-- Suggestion --"
    printf '%s\n' "${a_output_suggestion[@]}"
  fi
}
BASH
    ],

    // --- 5.3.3.1.2 Ensure password unlock time is configured ---
    [
        'id' => '5.3.3.1.2', 'title' => 'Ensure password unlock time is configured', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  FAILLOCK_CONF_FILE="/etc/security/faillock.conf"
  PAM_COMMON_AUTH_FILE="/etc/pam.d/common-auth"

  a_output_pass=()
  a_output_fail=()
  a_output_info=()
  a_output_suggestion=()
  AUDIT_OVERALL_STATUS="PASS"

  a_output_info+=("Checking '$FAILLOCK_CONF_FILE' for 'unlock_time' setting (should be 0 or >= 900).")
  if [ ! -f "$FAILLOCK_CONF_FILE" ]; then
    a_output_fail+=(" - Configuration file '$FAILLOCK_CONF_FILE' does not exist.")
    a_output_info+=("  - File: '$FAILLOCK_CONF_FILE' - Not found. This audit check cannot be performed fully.")
    AUDIT_OVERALL_STATUS="FAIL"
  else
    if grep -Piq '^\h*unlock_time\h*=\h*(0|9[0-9][0-9]|[1-9][0-9]{3,})\b' "$FAILLOCK_CONF_FILE"; then
      FOUND_UNLOCK_TIME=$(grep -Pi -- '^\h*unlock_time\h*=\h*(0|9[0-9][0-9]|[1-9][0-9]{3,})\b' "$FAILLOCK_CONF_FILE")
      a_output_pass+=(" - '$FAILLOCK_CONF_FILE' contains 'unlock_time' correctly configured: '$FOUND_UNLOCK_TIME'.")
      a_output_info+=("  - '$FAILLOCK_CONF_FILE' 'unlock_time' setting found: '$FOUND_UNLOCK_TIME'. This is within the recommended range (0 or >= 900).")
    else
      EXISTING_UNLOCK_TIME=$(grep -Pi -- '^\h*unlock_time\h*=\h*\d+\b' "$FAILLOCK_CONF_FILE" | awk -F'=' '{print $2}' | tr -d '[:space:]')
      if [[ -n "$EXISTING_UNLOCK_TIME" ]]; then
        a_output_fail+=(" - '$FAILLOCK_CONF_FILE' 'unlock_time' setting is '$EXISTING_UNLOCK_TIME', which is not 0 or >= 900. Should be 0 or >= 900.")
        a_output_info+=("  - '$FAILLOCK_CONF_FILE' 'unlock_time' setting found: '$EXISTING_UNLOCK_TIME'. This is outside the recommended range.")
        AUDIT_OVERALL_STATUS="FAIL"
        a_output_suggestion+=("Edit '$FAILLOCK_CONF_FILE' and set 'unlock_time' to '0' (never unlock) or '900' (15 minutes) or more (e.g., 'unlock_time = 900').")
      else
        a_output_fail+=(" - '$FAILLOCK_CONF_FILE' does not explicitly contain an 'unlock_time' setting within the recommended range.")
        a_output_info+=("  - '$FAILLOCK_CONF_FILE' 'unlock_time' setting not found or is commented out. It should be explicitly set.")
        AUDIT_OVERALL_STATUS="FAIL"
        a_output_suggestion+=("Add or uncomment the 'unlock_time' setting in '$FAILLOCK_CONF_FILE' and set it to '0' (never unlock) or '900' (15 minutes) or more (e.g., 'unlock_time = 900').")
      fi
    fi
  fi

  a_output_info+=("Checking '$PAM_COMMON_AUTH_FILE' for 'unlock_time' argument in pam_faillock.so (should NOT be 1-899).")
  if [ ! -f "$PAM_COMMON_AUTH_FILE" ]; then
    a_output_fail+=(" - PAM common-auth file '$PAM_COMMON_AUTH_FILE' does not exist.")
    a_output_info+=("  - File: '$PAM_COMMON_AUTH_FILE' - Not found. This audit check cannot be performed fully.")
    AUDIT_OVERALL_STATUS="FAIL"
  else
    if grep -Piq '^\h*auth\h+(?:requisite|required|sufficient)\h+pam_faillock\.so\h+([^#\n\r]+\h+)?unlock_time\h*=\h*([1-9]|[1-9][0-9]|[1-8][0-9][0-9])\b' "$PAM_COMMON_AUTH_FILE"; then
      FOUND_PAM_AUTH_UNLOCK_TIME=$(grep -Pi -- '^\h*auth\h+(requisite|required|sufficient)\h+pam_faillock\.so\h+([^#\n\r]+\h+)?unlock_time\h*=\h*([1-9]|[1-9][0-9]|[1-8][0-9][0-9])\b' "$PAM_COMMON_AUTH_FILE")
      a_output_fail+=(" - '$PAM_COMMON_AUTH_FILE' contains 'unlock_time' argument set to an unauthorized value (1-899): '$FOUND_PAM_AUTH_UNLOCK_TIME'.")
      a_output_info+=("  - '$PAM_COMMON_AUTH_FILE' 'unlock_time' argument found: '$FOUND_PAM_AUTH_UNLOCK_TIME'. This is an unauthorized override.")
      AUDIT_OVERALL_STATUS="FAIL"
      a_output_suggestion+=("Remove or modify the 'unlock_time' argument from the 'pam_faillock.so' line(s) in '$PAM_COMMON_AUTH_FILE' if it sets an unauthorized value (1-899).")
    else
      a_output_pass+=(" - '$PAM_COMMON_AUTH_FILE' 'unlock_time' argument in pam_faillock.so is not set to an unauthorized value (1-899).")
      a_output_info+=("  - '$PAM_COMMON_AUTH_FILE' 'unlock_time' argument in pam_faillock.so is not overriding to an unauthorized value.")
    fi
  fi

  echo ""
  echo "-- INFO --"
  printf '%s\n' "${a_output_info[@]}"

  echo ""
  echo "- Audit Result:"
  if [ "$AUDIT_OVERALL_STATUS" == "PASS" ]; then
    echo "  ** PASS **"
  else
    echo "  ** FAIL **"
  fi

  if [ "${#a_output_fail[@]}" -gt 0 ]; then
    echo "  - Reason(s) for audit failure:"
    printf '%s\n' "${a_output_fail[@]}"
  fi

  if [ "${#a_output_pass[@]}" -gt 0 ]; then
    echo ""
    echo "  - Correctly set:"
    printf '%s\n' "${a_output_pass[@]}"
  fi

  if [ "${#a_output_suggestion[@]}" -gt 0 ]; then
    echo ""
    echo "-- Suggestion --"
    printf '%s\n' "${a_output_suggestion[@]}"
  fi
}
BASH
    ],

    // --- 5.3.3.1.3 Ensure password failed attempts lockout includes root account ---
    [
        'id' => '5.3.3.1.3', 'title' => 'Ensure password failed attempts lockout includes root account', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
  FAILLOCK_CONF_FILE="/etc/security/faillock.conf"
  PAM_COMMON_AUTH_FILE="/etc/pam.d/common-auth"

  a_output_pass=()
  a_output_fail=()
  a_output_info=()
  a_output_suggestion=()
  AUDIT_OVERALL_STATUS="PASS"

  a_output_info+=("Checking '$FAILLOCK_CONF_FILE' for 'even_deny_root' and 'root_unlock_time' (ensuring root lockout).")
  FOUND_ROOT_LOCKOUT_CONF=$(grep -Pi -- '^\h*(even_deny_root|root_unlock_time\h*=\h*\d+)\b' "$FAILLOCK_CONF_FILE")

  if [[ -n "$FOUND_ROOT_LOCKOUT_CONF" ]]; then
    a_output_info+=("  - '$FAILLOCK_CONF_FILE' contains: '$FOUND_ROOT_LOCKOUT_CONF'. This indicates root lockout is configured.")
    if grep -Piq '^\h*root_unlock_time\h*=\h*([1-9]|[1-5][0-9])\b' "$FAILLOCK_CONF_FILE"; then
      BAD_ROOT_UNLOCK_TIME_CONF=$(grep -Pi -- '^\h*root_unlock_time\h*=\h*([1-9]|[1-5][0-9])\b' "$FAILLOCK_CONF_FILE")
      a_output_fail+=(" - '$FAILLOCK_CONF_FILE' 'root_unlock_time' is set to '$BAD_ROOT_UNLOCK_TIME_CONF', which is less than 60 seconds.")
      a_output_info+=("  - '$FAILLOCK_CONF_FILE' 'root_unlock_time' is found but its value is too low.")
      AUDIT_OVERALL_STATUS="FAIL"
      a_output_suggestion+=("Edit '$FAILLOCK_CONF_FILE' and ensure 'root_unlock_time' is set to 60 or more, or remove it if 'even_deny_root' is sufficient based on policy.")
    else
      a_output_pass+=(" - '$FAILLOCK_CONF_FILE' 'root_unlock_time' (if set) is 60 or more, or is not set to a problematic value.")
      a_output_info+=("  - '$FAILLOCK_CONF_FILE' 'root_unlock_time' is correctly configured or not set to a problematic value.")
    fi
  else
    a_output_fail+=(" - Neither 'even_deny_root' nor 'root_unlock_time' is explicitly enabled in '$FAILLOCK_CONF_FILE'. Root account lockout is not configured.")
    a_output_info+=("  - Root lockout is NOT explicitly configured in '$FAILLOCK_CONF_FILE'.")
    AUDIT_OVERALL_STATUS="FAIL"
    a_output_suggestion+=("Edit '$FAILLOCK_CONF_FILE' and add 'even_deny_root' or set 'root_unlock_time' to a value of 60 or more (e.g., 'even_deny_root' or 'root_unlock_time = 60').")
  fi

  a_output_info+=("Checking '$PAM_COMMON_AUTH_FILE' for 'root_unlock_time' argument (should NOT be 1-59).")
  if [ ! -f "$PAM_COMMON_AUTH_FILE" ]; then
    a_output_fail+=(" - PAM common-auth file '$PAM_COMMON_AUTH_FILE' does not exist.")
    a_output_info+=("  - File: '$PAM_COMMON_AUTH_FILE' - Not found. This audit check cannot be performed fully.")
    AUDIT_OVERALL_STATUS="FAIL"
  else
    if grep -Piq '^\h*auth\h+([^#\n\r]+\h+)pam_faillock\.so\h+([^#\n\r]+\h+)?root_unlock_time\h*=\h*([1-9]|[1-5][0-9])\b' "$PAM_COMMON_AUTH_FILE"; then
      BAD_PAM_AUTH_ROOT_UNLOCK_TIME=$(grep -Pi -- '^\h*auth\h+([^#\n\r]+\h+)pam_faillock\.so\h+([^#\n\r]+\h+)?root_unlock_time\h*=\h*([1-9]|[1-5][0-9])\b' "$PAM_COMMON_AUTH_FILE")
      a_output_fail+=(" - '$PAM_COMMON_AUTH_FILE' contains 'root_unlock_time' argument set to an unauthorized value (1-59): '$BAD_PAM_AUTH_ROOT_UNLOCK_TIME'.")
      a_output_info+=("  - '$PAM_COMMON_AUTH_FILE' 'root_unlock_time' argument found, and its value is too low. This is an unauthorized override.")
      AUDIT_OVERALL_STATUS="FAIL"
      a_output_suggestion+=("Edit '$PAM_COMMON_AUTH_FILE' and remove or modify the 'root_unlock_time' argument from the 'pam_faillock.so' line(s) if it sets an unauthorized value (1-59).")
    else
      a_output_pass+=(" - '$PAM_COMMON_AUTH_FILE' 'root_unlock_time' argument in pam_faillock.so is not set to an unauthorized value (1-59).")
      a_output_info+=("  - '$PAM_COMMON_AUTH_FILE' 'root_unlock_time' argument in pam_faillock.so is not overriding to an unauthorized value.")
    fi
  fi

  echo ""
  echo "-- INFO --"
  printf '%s\n' "${a_output_info[@]}"

  echo ""
  echo "- Audit Result:"
  if [ "$AUDIT_OVERALL_STATUS" == "PASS" ]; then
    echo "  ** PASS **"
  else
    echo "  ** FAIL **"
  fi

  if [ "${#a_output_fail[@]}" -gt 0 ]; then
    echo "  - Reason(s) for audit failure:"
    printf '%s\n' "${a_output_fail[@]}"
  fi

  if [ "${#a_output_pass[@]}" -gt 0 ]; then
    echo ""
    echo "  - Correctly set:"
    printf '%s\n' "${a_output_pass[@]}"
  fi

  if [ "${#a_output_suggestion[@]}" -gt 0 ]; then
    echo ""
    echo "-- Suggestion --"
    printf '%s\n' "${a_output_suggestion[@]}"
  fi
}
BASH
    ],

    // --- 5.3.3.2 Configure pam_pwquality module ---
    ['id' => '5.3.3.2', 'title' => 'Configure pam_pwquality module', 'type' => 'header'],

    // --- 5.3.3.2.1 Ensure password number of changed characters is configured  ---
    [
        'id' => '5.3.3.2.1', 'title' => 'Ensure password number of changed characters is configured', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
  FAILLOCK_CONF_FILE="/etc/security/faillock.conf"
  PAM_COMMON_AUTH_FILE="/etc/pam.d/common-auth"

  a_output_pass=()
  a_output_fail=()
  a_output_info=()
  a_output_suggestion=()
  AUDIT_OVERALL_STATUS="PASS"

  a_output_info+=("Checking '$FAILLOCK_CONF_FILE' for 'even_deny_root' and 'root_unlock_time' (ensuring root lockout).")
  FOUND_ROOT_LOCKOUT_CONF=$(grep -Pi -- '^\h*(even_deny_root|root_unlock_time\h*=\h*\d+)\b' "$FAILLOCK_CONF_FILE")

  if [[ -n "$FOUND_ROOT_LOCKOUT_CONF" ]]; then
    a_output_info+=("  - '$FAILLOCK_CONF_FILE' contains: '$FOUND_ROOT_LOCKOUT_CONF'. This indicates root lockout is configured.")
    if grep -Piq '^\h*root_unlock_time\h*=\h*([1-9]|[1-5][0-9])\b' "$FAILLOCK_CONF_FILE"; then
      BAD_ROOT_UNLOCK_TIME_CONF=$(grep -Pi -- '^\h*root_unlock_time\h*=\h*([1-9]|[1-5][0-9])\b' "$FAILLOCK_CONF_FILE")
      a_output_fail+=(" - '$FAILLOCK_CONF_FILE' 'root_unlock_time' is set to '$BAD_ROOT_UNLOCK_TIME_CONF', which is less than 60 seconds.")
      a_output_info+=("  - '$FAILLOCK_CONF_FILE' 'root_unlock_time' is found but its value is too low.")
      AUDIT_OVERALL_STATUS="FAIL"
      a_output_suggestion+=("Edit '$FAILLOCK_CONF_FILE' and ensure 'root_unlock_time' is set to 60 or more, or remove it if 'even_deny_root' is sufficient based on policy.")
    else
      a_output_pass+=(" - '$FAILLOCK_CONF_FILE' 'root_unlock_time' (if set) is 60 or more, or is not set to a problematic value.")
      a_output_info+=("  - '$FAILLOCK_CONF_FILE' 'root_unlock_time' is correctly configured or not set to a problematic value.")
    fi
  else
    a_output_fail+=(" - Neither 'even_deny_root' nor 'root_unlock_time' is explicitly enabled in '$FAILLOCK_CONF_FILE'. Root account lockout is not configured.")
    a_output_info+=("  - Root lockout is NOT explicitly configured in '$FAILLOCK_CONF_FILE'.")
    AUDIT_OVERALL_STATUS="FAIL"
    a_output_suggestion+=("Edit '$FAILLOCK_CONF_FILE' and add 'even_deny_root' or set 'root_unlock_time' to a value of 60 or more (e.g., 'even_deny_root' or 'root_unlock_time = 60').")
  fi

  a_output_info+=("Checking '$PAM_COMMON_AUTH_FILE' for 'root_unlock_time' argument (should NOT be 1-59).")
  if [ ! -f "$PAM_COMMON_AUTH_FILE" ]; then
    a_output_fail+=(" - PAM common-auth file '$PAM_COMMON_AUTH_FILE' does not exist.")
    a_output_info+=("  - File: '$PAM_COMMON_AUTH_FILE' - Not found. This audit check cannot be performed fully.")
    AUDIT_OVERALL_STATUS="FAIL"
  else
    if grep -Piq '^\h*auth\h+([^#\n\r]+\h+)pam_faillock\.so\h+([^#\n\r]+\h+)?root_unlock_time\h*=\h*([1-9]|[1-5][0-9])\b' "$PAM_COMMON_AUTH_FILE"; then
      BAD_PAM_AUTH_ROOT_UNLOCK_TIME=$(grep -Pi -- '^\h*auth\h+([^#\n\r]+\h+)pam_faillock\.so\h+([^#\n\r]+\h+)?root_unlock_time\h*=\h*([1-9]|[1-5][0-9])\b' "$PAM_COMMON_AUTH_FILE")
      a_output_fail+=(" - '$PAM_COMMON_AUTH_FILE' contains 'root_unlock_time' argument set to an unauthorized value (1-59): '$BAD_PAM_AUTH_ROOT_UNLOCK_TIME'.")
      a_output_info+=("  - '$PAM_COMMON_AUTH_FILE' 'root_unlock_time' argument found, and its value is too low. This is an unauthorized override.")
      AUDIT_OVERALL_STATUS="FAIL"
      a_output_suggestion+=("Edit '$PAM_COMMON_AUTH_FILE' and remove or modify the 'root_unlock_time' argument from the 'pam_faillock.so' line(s) if it sets an unauthorized value (1-59).")
    else
      a_output_pass+=(" - '$PAM_COMMON_AUTH_FILE' 'root_unlock_time' argument in pam_faillock.so is not set to an unauthorized value (1-59).")
      a_output_info+=("  - '$PAM_COMMON_AUTH_FILE' 'root_unlock_time' argument in pam_faillock.so is not overriding to an unauthorized value.")
    fi
  fi

  echo ""
  echo "-- INFO --"
  printf '%s\n' "${a_output_info[@]}"

  echo ""
  echo "- Audit Result:"
  if [ "$AUDIT_OVERALL_STATUS" == "PASS" ]; then
    echo "  ** PASS **"
  else
    echo "  ** FAIL **"
  fi

  if [ "${#a_output_fail[@]}" -gt 0 ]; then
    echo "  - Reason(s) for audit failure:"
    printf '%s\n' "${a_output_fail[@]}"
  fi

  if [ "${#a_output_pass[@]}" -gt 0 ]; then
    echo ""
    echo "  - Correctly set:"
    printf '%s\n' "${a_output_pass[@]}"
  fi

  if [ "${#a_output_suggestion[@]}" -gt 0 ]; then
    echo ""
    echo "-- Suggestion --"
    printf '%s\n' "${a_output_suggestion[@]}"
  fi
}
BASH
    ],

    // --- 5.3.3.2.2 Ensure minimum password length is configured ---
    [
        'id' => '5.3.3.2.2', 'title' => 'Ensure minimum password length is configured', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
  FAILLOCK_CONF_FILE="/etc/security/faillock.conf"
  PAM_COMMON_AUTH_FILE="/etc/pam.d/common-auth"

  a_output_pass=()
  a_output_fail=()
  a_output_info=()
  a_output_suggestion=()
  AUDIT_OVERALL_STATUS="PASS"

  a_output_info+=("Checking '$FAILLOCK_CONF_FILE' for 'even_deny_root' and 'root_unlock_time' (ensuring root lockout).")
  FOUND_ROOT_LOCKOUT_CONF=$(grep -Pi -- '^\h*(even_deny_root|root_unlock_time\h*=\h*\d+)\b' "$FAILLOCK_CONF_FILE")

  if [[ -n "$FOUND_ROOT_LOCKOUT_CONF" ]]; then
    a_output_info+=("  - '$FAILLOCK_CONF_FILE' contains: '$FOUND_ROOT_LOCKOUT_CONF'. This indicates root lockout is configured.")
    if grep -Piq '^\h*root_unlock_time\h*=\h*([1-9]|[1-5][0-9])\b' "$FAILLOCK_CONF_FILE"; then
      BAD_ROOT_UNLOCK_TIME_CONF=$(grep -Pi -- '^\h*root_unlock_time\h*=\h*([1-9]|[1-5][0-9])\b' "$FAILLOCK_CONF_FILE")
      a_output_fail+=(" - '$FAILLOCK_CONF_FILE' 'root_unlock_time' is set to '$BAD_ROOT_UNLOCK_TIME_CONF', which is less than 60 seconds.")
      a_output_info+=("  - '$FAILLOCK_CONF_FILE' 'root_unlock_time' is found but its value is too low.")
      AUDIT_OVERALL_STATUS="FAIL"
      a_output_suggestion+=("Edit '$FAILLOCK_CONF_FILE' and ensure 'root_unlock_time' is set to 60 or more, or remove it if 'even_deny_root' is sufficient based on policy.")
    else
      a_output_pass+=(" - '$FAILLOCK_CONF_FILE' 'root_unlock_time' (if set) is 60 or more, or is not set to a problematic value.")
      a_output_info+=("  - '$FAILLOCK_CONF_FILE' 'root_unlock_time' is correctly configured or not set to a problematic value.")
    fi
  else
    a_output_fail+=(" - Neither 'even_deny_root' nor 'root_unlock_time' is explicitly enabled in '$FAILLOCK_CONF_FILE'. Root account lockout is not configured.")
    a_output_info+=("  - Root lockout is NOT explicitly configured in '$FAILLOCK_CONF_FILE'.")
    AUDIT_OVERALL_STATUS="FAIL"
    a_output_suggestion+=("Edit '$FAILLOCK_CONF_FILE' and add 'even_deny_root' or set 'root_unlock_time' to a value of 60 or more (e.g., 'even_deny_root' or 'root_unlock_time = 60').")
  fi

  a_output_info+=("Checking '$PAM_COMMON_AUTH_FILE' for 'root_unlock_time' argument (should NOT be 1-59).")
  if [ ! -f "$PAM_COMMON_AUTH_FILE" ]; then
    a_output_fail+=(" - PAM common-auth file '$PAM_COMMON_AUTH_FILE' does not exist.")
    a_output_info+=("  - File: '$PAM_COMMON_AUTH_FILE' - Not found. This audit check cannot be performed fully.")
    AUDIT_OVERALL_STATUS="FAIL"
  else
    if grep -Piq '^\h*auth\h+([^#\n\r]+\h+)pam_faillock\.so\h+([^#\n\r]+\h+)?root_unlock_time\h*=\h*([1-9]|[1-5][0-9])\b' "$PAM_COMMON_AUTH_FILE"; then
      BAD_PAM_AUTH_ROOT_UNLOCK_TIME=$(grep -Pi -- '^\h*auth\h+([^#\n\r]+\h+)pam_faillock\.so\h+([^#\n\r]+\h+)?root_unlock_time\h*=\h*([1-9]|[1-5][0-9])\b' "$PAM_COMMON_AUTH_FILE")
      a_output_fail+=(" - '$PAM_COMMON_AUTH_FILE' contains 'root_unlock_time' argument set to an unauthorized value (1-59): '$BAD_PAM_AUTH_ROOT_UNLOCK_TIME'.")
      a_output_info+=("  - '$PAM_COMMON_AUTH_FILE' 'root_unlock_time' argument found, and its value is too low. This is an unauthorized override.")
      AUDIT_OVERALL_STATUS="FAIL"
      a_output_suggestion+=("Edit '$PAM_COMMON_AUTH_FILE' and remove or modify the 'root_unlock_time' argument from the 'pam_faillock.so' line(s) if it sets an unauthorized value (1-59).")
    else
      a_output_pass+=(" - '$PAM_COMMON_AUTH_FILE' 'root_unlock_time' argument in pam_faillock.so is not set to an unauthorized value (1-59).")
      a_output_info+=("  - '$PAM_COMMON_AUTH_FILE' 'root_unlock_time' argument in pam_faillock.so is not overriding to an unauthorized value.")
    fi
  fi

  echo ""
  echo "-- INFO --"
  printf '%s\n' "${a_output_info[@]}"

  echo ""
  echo "- Audit Result:"
  if [ "$AUDIT_OVERALL_STATUS" == "PASS" ]; then
    echo "  ** PASS **"
  else
    echo "  ** FAIL **"
  fi

  if [ "${#a_output_fail[@]}" -gt 0 ]; then
    echo "  - Reason(s) for audit failure:"
    printf '%s\n' "${a_output_fail[@]}"
  fi

  if [ "${#a_output_pass[@]}" -gt 0 ]; then
    echo ""
    echo "  - Correctly set:"
    printf '%s\n' "${a_output_pass[@]}"
  fi

  if [ "${#a_output_suggestion[@]}" -gt 0 ]; then
    echo ""
    echo "-- Suggestion --"
    printf '%s\n' "${a_output_suggestion[@]}"
  fi
}
BASH
    ],

    // --- 5.3.3.2.3 Ensure password complexity is configured ---
    [
        'id' => '5.3.3.2.3', 'title' => 'Ensure password complexity is configured', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  PWQUALITY_CONF_DIR="/etc/security/pwquality.conf.d"
  PWQUALITY_CONF_FILE="/etc/security/pwquality.conf"
  PAM_COMMON_PASSWORD_FILE="/etc/pam.d/common-password"

  a_output_pass=()
  a_output_fail=()
  a_output_info=()
  a_output_suggestion=()
  AUDIT_OVERALL_STATUS="PASS"

  a_output_info+=("Checking 'minclass', 'dcredit', 'ucredit', 'lcredit', and 'ocredit' in pwquality configuration files.")

  declare -A current_settings=(
    ["minclass"]=""
    ["dcredit"]=""
    ["ucredit"]=""
    ["lcredit"]=""
    ["ocredit"]=""
  )
  declare -A current_setting_files=(
    ["minclass"]=""
    ["dcredit"]=""
    ["ucredit"]=""
    ["lcredit"]=""
    ["ocredit"]=""
  )

  # Function to get the effective setting based on precedence
  get_effective_setting() {
    local param_name="$1"
    local found_value=""
    local found_file=""

    # 1. Check .conf files in the directory (highest precedence)
    for conf_file in "$PWQUALITY_CONF_DIR"/*.conf; do
        if [[ -f "$conf_file" ]]; then
            CURRENT_FILE_VALUE=$(grep -Pi -- "^\h*${param_name}\h*=\h*(-?[0-9]+)\b" "$conf_file" | tail -n 1 | awk -F'=' '{print $2}' | tr -d '[:space:]')
            if [[ -n "$CURRENT_FILE_VALUE" ]]; then
                found_value="$CURRENT_FILE_VALUE"
                found_file="$conf_file"
            fi
        fi
    done

    # 2. Check main pwquality.conf file (lower precedence than .d/ files)
    if [[ -z "$found_value" && -f "$PWQUALITY_CONF_FILE" ]]; then
        CURRENT_FILE_VALUE=$(grep -Pi -- "^\h*${param_name}\h*=\h*(-?[0-9]+)\b" "$PWQUALITY_CONF_FILE" | tail -n 1 | awk -F'=' '{print $2}' | tr -d '[:space:]')
        if [[ -n "$CURRENT_FILE_VALUE" ]]; then
            found_value="$CURRENT_FILE_VALUE"
            found_file="$PWQUALITY_CONF_FILE"
        fi
    fi

    echo "$found_value;$found_file" # Return value and file separated by semicolon
  }

  # Populate current settings
  for param in "${!current_settings[@]}"; do
    READ_OUTPUT=$(get_effective_setting "$param")
    IFS=';' read -r current_settings["$param"] current_setting_files["$param"] <<< "$READ_OUTPUT"
  done

  # Audit for 'dcredit', 'ucredit', 'lcredit', 'ocredit' not greater than 0
  for credit_param in "dcredit" "ucredit" "lcredit" "ocredit"; do
    value="${current_settings[$credit_param]}"
    file="${current_setting_files[$credit_param]}"

    if [[ -n "$value" ]]; then
      if (( value > 0 )); then
        a_output_fail+=(" - '$credit_param' in '$file' is set to '$value', which is greater than 0. This is not recommended for credit options.")
        a_output_info+=("  - '$credit_param' setting found in '$file': '$value'. This fails as it's > 0.")
        AUDIT_OVERALL_STATUS="FAIL"
        a_output_suggestion+=("Edit '$file' and set '$credit_param' to 0 or a negative value (e.g., '${credit_param} = 0' or '${credit_param} = -1').")
      else
        a_output_pass+=(" - '$credit_param' in '$file' is set to '$value', which is 0 or less. (Correct)")
        a_output_info+=("  - '$credit_param' setting found in '$file': '$value'. This passes as it's <= 0.")
      fi
    else
        # If the parameter is not explicitly set in config files, it uses default 0, which is acceptable for d/u/l/ocredit
        a_output_pass+=(" - '$credit_param' is not explicitly set in config files. Defaulting to 0, which is acceptable.")
        a_output_info+=("  - '$credit_param' not explicitly configured in pwquality files. Assumed default is 0 (acceptable).")
    fi
  done

  # Audit for 'minclass' (Complexity conforms to local site policy)
  # NOTE: "local site policy" is subjective and cannot be fully automated.
  # The script will verify that it is *set* and report its value for manual review.
  value="${current_settings['minclass']}"
  file="${current_setting_files['minclass']}"

  if [[ -n "$value" ]]; then
    a_output_info+=("  - 'minclass' setting found in '$file': '$value'. Please verify this conforms to local site policy (e.g., 'minclass = 3').")
    a_output_pass+=(" - 'minclass' is set to '$value' in '$file'. (Requires manual verification against local site policy).")
  else
    a_output_fail+=(" - 'minclass' is not explicitly set in pwquality configuration files. It should be configured according to local site policy.")
    a_output_info+=("  - 'minclass' not explicitly configured in pwquality files.")
    AUDIT_OVERALL_STATUS="FAIL"
    a_output_suggestion+=("Add or uncomment 'minclass' setting in '$PWQUALITY_CONF_FILE' or a .conf file in '$PWQUALITY_CONF_DIR' and set it according to local site policy (e.g., 'minclass = 3').")
  fi


  a_output_info+=("Checking PAM file '$PAM_COMMON_PASSWORD_FILE' for 'minclass' or '[dulo]credit' arguments (should NOT override to unauthorized values).")
  # Audit 2: Verify that module arguments in the configuration file(s) are not being overridden by arguments in /etc/pam.d/common-password
  # This pattern matches any of the complexity parameters set directly as module arguments in common-password
  PAM_OVERRIDE_CHECK=$(grep -Psi -- '^\h*password\h+(?:requisite|required|sufficient)\h+pam_pwquality\.so\h+([^#\n\r]+\h+)?(minclass=\d*|[dulo]credit=-?\d*)\b' "$PAM_COMMON_PASSWORD_FILE")

  if [ ! -f "$PAM_COMMON_PASSWORD_FILE" ]; then
    a_output_fail+=(" - PAM common-password file '$PAM_COMMON_PASSWORD_FILE' does not exist.")
    a_output_info+=("  - File: '$PAM_COMMON_PASSWORD_FILE' - Not found. This audit check cannot be performed fully.")
    AUDIT_OVERALL_STATUS="FAIL"
  elif [[ -n "$PAM_OVERRIDE_CHECK" ]]; then
    a_output_fail+=(" - '$PAM_COMMON_PASSWORD_FILE' contains pam_pwquality.so arguments that might override central configuration: '$PAM_OVERRIDE_CHECK'.")
    a_output_info+=("  - '$PAM_COMMON_PASSWORD_FILE' contains direct 'pam_pwquality.so' arguments. This may lead to configuration inconsistencies as per precedence rules.")
    AUDIT_OVERALL_STATUS="FAIL"
    a_output_suggestion+=("Remove or modify direct 'minclass' or '[dulo]credit' arguments from the 'pam_pwquality.so' line(s) in '$PAM_COMMON_PASSWORD_FILE' to ensure settings are managed in '$PWQUALITY_CONF_FILE' or '$PWQUALITY_CONF_DIR'.")
  else
    a_output_pass+=(" - '$PAM_COMMON_PASSWORD_FILE' does not contain direct 'minclass' or '[dulo]credit' arguments for pam_pwquality.so.")
    a_output_info+=("  - '$PAM_COMMON_PASSWORD_FILE' is not overriding central 'pwquality' settings for complexity arguments.")
  fi

  echo "-- INFO --"
  printf '%s\n' "${a_output_info[@]}"

  echo ""
  echo "- Audit Result:"
  if [ "$AUDIT_OVERALL_STATUS" == "PASS" ]; then
    echo "  ** PASS **"
  else
    echo "  ** FAIL **"
  fi

  if [ "${#a_output_fail[@]}" -gt 0 ]; then
    echo "  - Reason(s) for audit failure:"
    printf '%s\n' "${a_output_fail[@]}"
  fi

  if [ "${#a_output_pass[@]}" -gt 0 ]; then
    echo ""
    echo "  - Correctly set:"
    printf '%s\n' "${a_output_pass[@]}"
  fi

  if [ "${#a_output_suggestion[@]}" -gt 0 ]; then
    echo ""
    echo "-- Suggestion --"
    printf '%s\n' "${a_output_suggestion[@]}"
  fi
}
BASH
    ],

    // --- 5.3.3.2.4 Ensure password same consecutive characters is configured ---
    [
        'id' => '5.3.3.2.4', 'title' => 'Ensure password same consecutive characters is configured', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  PWQUALITY_CONF_DIR="/etc/security/pwquality.conf.d"
  PWQUALITY_CONF_FILE="/etc/security/pwquality.conf"
  PAM_COMMON_PASSWORD_FILE="/etc/pam.d/common-password"

  a_output_pass=()
  a_output_fail=()
  a_output_info=()
  a_output_suggestion=()
  AUDIT_OVERALL_STATUS="PASS"

  a_output_info+=("Checking 'maxrepeat' option in pwquality configuration files (should be 1-3).")

  LATEST_MAXREPEAT_VALUE=""
  LATEST_MAXREPEAT_FILE=""

  for conf_file in "$PWQUALITY_CONF_DIR"/*.conf; do
      if [[ -f "$conf_file" ]]; then
          CURRENT_FILE_MAXREPEAT=$(grep -Pi -- '^\h*maxrepeat\h*=\h*([0-9]+)\b' "$conf_file" | tail -n 1 | awk -F'=' '{print $2}' | tr -d '[:space:]')
          if [[ -n "$CURRENT_FILE_MAXREPEAT" ]]; then
              LATEST_MAXREPEAT_VALUE="$CURRENT_FILE_MAXREPEAT"
              LATEST_MAXREPEAT_FILE="$conf_file"
          fi
      fi
  done

  if [[ -z "$LATEST_MAXREPEAT_VALUE" && -f "$PWQUALITY_CONF_FILE" ]]; then
      CURRENT_FILE_MAXREPEAT=$(grep -Pi -- '^\h*maxrepeat\h*=\h*([0-9]+)\b' "$PWQUALITY_CONF_FILE" | tail -n 1 | awk -F'=' '{print $2}' | tr -d '[:space:]')
      if [[ -n "$CURRENT_FILE_MAXREPEAT" ]]; then
          LATEST_MAXREPEAT_VALUE="$CURRENT_FILE_MAXREPEAT"
          LATEST_MAXREPEAT_FILE="$PWQUALITY_CONF_FILE"
      fi
  fi

  if [[ -n "$LATEST_MAXREPEAT_VALUE" ]]; then
      if (( LATEST_MAXREPEAT_VALUE >= 1 && LATEST_MAXREPEAT_VALUE <= 3 )); then
          a_output_pass+=(" - 'maxrepeat' option in '$LATEST_MAXREPEAT_FILE' is correctly set to '$LATEST_MAXREPEAT_VALUE' (1-3).")
          a_output_info+=("  - 'maxrepeat' setting found in '$LATEST_MAXREPEAT_FILE': '$LATEST_MAXREPEAT_VALUE'. This meets the requirement.")
      else
          a_output_fail+=(" - 'maxrepeat' option in '$LATEST_MAXREPEAT_FILE' is set to '$LATEST_MAXREPEAT_VALUE', which is not within the 1-3 range or is 0.")
          a_output_info+=("  - 'maxrepeat' setting found in '$LATEST_MAXREPEAT_FILE': '$LATEST_MAXREPEAT_VALUE'. This fails the requirement.")
          AUDIT_OVERALL_STATUS="FAIL"
          a_output_suggestion+=("Edit '$LATEST_MAXREPEAT_FILE' and set 'maxrepeat' to a value between 1 and 3 (e.g., 'maxrepeat = 3').")
      fi
  else
      a_output_fail+=(" - 'maxrepeat' option is not explicitly set to a value between 1-3 in '$PWQUALITY_CONF_FILE' or '$PWQUALITY_CONF_DIR'/*.conf.")
      a_output_info+=("  - 'maxrepeat' setting not found in main config or drop-in files, or is commented out. It should be explicitly set.")
      AUDIT_OVERALL_STATUS="FAIL"
      a_output_suggestion+=("Add or uncomment 'maxrepeat' setting in '$PWQUALITY_CONF_FILE' or a .conf file in '$PWQUALITY_CONF_DIR' and set it to a value between 1 and 3 (e.g., 'maxrepeat = 3').")
  fi


  a_output_info+=("Checking PAM file '$PAM_COMMON_PASSWORD_FILE' for 'maxrepeat' argument (should NOT be 0 or >3).")
  if [ ! -f "$PAM_COMMON_PASSWORD_FILE" ]; then
    a_output_fail+=(" - PAM common-password file '$PAM_COMMON_PASSWORD_FILE' does not exist.")
    a_output_info+=("  - File: '$PAM_COMMON_PASSWORD_FILE' - Not found. This audit check cannot be performed fully.")
    AUDIT_OVERALL_STATUS="FAIL"
  else
    if grep -Piq '^\h*password\h+(?:requisite|required|sufficient)\h+pam_pwquality\.so\h+([^#\n\r]+\h+)?maxrepeat\h*=\h*(0|[4-9]|[1-9][0-9]+)\b' "$PAM_COMMON_PASSWORD_FILE"; then
      FOUND_PAM_MAXREPEAT_ARG=$(grep -Pi -- '^\h*password\h+(requisite|required|sufficient)\h+pam_pwquality\.so\h+([^#\n\r]+\h+)?maxrepeat\h*=\h*(0|[4-9]|[1-9][0-9]+)\b' "$PAM_COMMON_PASSWORD_FILE")
      a_output_fail+=(" - '$PAM_COMMON_PASSWORD_FILE' contains 'maxrepeat' argument set to an unauthorized value (0 or >3): '$FOUND_PAM_MAXREPEAT_ARG'.")
      a_output_info+=("  - '$PAM_COMMON_PASSWORD_FILE' 'maxrepeat' argument found: '$FOUND_PAM_MAXREPEAT_ARG'. This is an unauthorized override.")
      AUDIT_OVERALL_STATUS="FAIL"
      a_output_suggestion+=("Remove or modify the 'maxrepeat' argument from the 'pam_pwquality.so' line(s) in '$PAM_COMMON_PASSWORD_FILE' if it sets an unauthorized value (0 or >3).")
    else
      a_output_pass+=(" - '$PAM_COMMON_PASSWORD_FILE' 'maxrepeat' argument in pam_pwquality.so is not set to an unauthorized value (0 or >3).")
      a_output_info+=("  - '$PAM_COMMON_PASSWORD_FILE' 'maxrepeat' argument in pam_pwquality.so is not overriding to an unauthorized value.")
    fi
  fi

  echo "-- INFO --"
  printf '%s\n' "${a_output_info[@]}"

  echo ""
  echo "- Audit Result:"
  if [ "$AUDIT_OVERALL_STATUS" == "PASS" ]; then
    echo "  ** PASS **"
  else
    echo "  ** FAIL **"
  fi

  if [ "${#a_output_fail[@]}" -gt 0 ]; then
    echo "  - Reason(s) for audit failure:"
    printf '%s\n' "${a_output_fail[@]}"
  fi

  if [ "${#a_output_pass[@]}" -gt 0 ]; then
    echo ""
    echo "  - Correctly set:"
    printf '%s\n' "${a_output_pass[@]}"
  fi

  if [ "${#a_output_suggestion[@]}" -gt 0 ]; then
    echo ""
    echo "-- Suggestion --"
    printf '%s\n' "${a_output_suggestion[@]}"
  fi
}
BASH
    ],

    // --- 5.3.3.2.5 Ensure password maximum sequential characters is configured ---
    [
        'id' => '5.3.3.2.5', 'title' => 'Ensure password maximum sequential characters is configured', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  PWQUALITY_CONF_DIR="/etc/security/pwquality.conf.d"
  PWQUALITY_CONF_FILE="/etc/security/pwquality.conf"
  PAM_COMMON_PASSWORD_FILE="/etc/pam.d/common-password"

  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL"

  LATEST_MAXSEQUENCE_VALUE=""
  LATEST_MAXSEQUENCE_FILE=""

  for conf_file in "$PWQUALITY_CONF_DIR"/*.conf; do
    if [[ -f "$conf_file" ]]; then
      CURRENT_VALUE=$(grep -Pi -- '^\h*maxsequence\h*=\h*([0-9]+)\b' "$conf_file" | tail -n 1 | awk -F'=' '{print $2}' | tr -d '[:space:]')
      if [[ -n "$CURRENT_VALUE" ]]; then
        LATEST_MAXSEQUENCE_VALUE="$CURRENT_VALUE"
        LATEST_MAXSEQUENCE_FILE="$conf_file"
      fi
    fi
  done

  if [[ -z "$LATEST_MAXSEQUENCE_VALUE" && -f "$PWQUALITY_CONF_FILE" ]]; then
    CURRENT_VALUE=$(grep -Pi -- '^\h*maxsequence\h*=\h*([0-9]+)\b' "$PWQUALITY_CONF_FILE" | tail -n 1 | awk -F'=' '{print $2}' | tr -d '[:space:]')
    if [[ -n "$CURRENT_VALUE" ]]; then
      LATEST_MAXSEQUENCE_VALUE="$CURRENT_VALUE"
      LATEST_MAXSEQUENCE_FILE="$PWQUALITY_CONF_FILE"
    fi
  fi

  if [[ -n "$LATEST_MAXSEQUENCE_VALUE" ]]; then
    if (( LATEST_MAXSEQUENCE_VALUE >= 1 && LATEST_MAXSEQUENCE_VALUE <= 3 )); then
      a_output_pass+=(" - 'maxsequence' in '$LATEST_MAXSEQUENCE_FILE' is '$LATEST_MAXSEQUENCE_VALUE' (correct).")
    else
      a_output_fail+=(" - 'maxsequence' in '$LATEST_MAXSEQUENCE_FILE' is '$LATEST_MAXSEQUENCE_VALUE' (incorrect, should be 1-3).")
    fi
  else
    a_output_fail+=(" - 'maxsequence' not explicitly set in pwquality config files (should be 1-3).")
  fi

  if [ ! -f "$PAM_COMMON_PASSWORD_FILE" ]; then
    a_output_fail+=(" - PAM common-password file '$PAM_COMMON_PASSWORD_FILE' does not exist.")
  else
    if grep -Piq '^\h*password\h+(?:requisite|required|sufficient)\h+pam_pwquality\.so\h+([^#\n\r]+\h+)?maxsequence\h*=\h*(0|[4-9]|[1-9][0-9]+)\b' "$PAM_COMMON_PASSWORD_FILE"; then
      FOUND_PAM_ARG=$(grep -Pi -- '^\h*password\h+(requisite|required|sufficient)\h+pam_pwquality\.so\h+([^#\n\r]+\h+)?maxsequence\h*=\h*(0|[4-9]|[1-9][0-9]+)\b' "$PAM_COMMON_PASSWORD_FILE")
      a_output_fail+=(" - '$PAM_COMMON_PASSWORD_FILE' overrides 'maxsequence' to unauthorized value: '$FOUND_PAM_ARG'.")
    else
      a_output_pass+=(" - '$PAM_COMMON_PASSWORD_FILE' does not override 'maxsequence' to an unauthorized value.")
    fi
  fi

  echo "- Audit Result:"
  if [ "${#a_output_fail[@]}" -eq 0 ]; then
    echo "  ** PASS **"
    AUDIT_OVERALL_STATUS="PASS"
    printf '%s\n' "${a_output_pass[@]}"
  else
    echo "  ** FAIL **"
    printf '%s\n' "${a_output_fail[@]}"
    if [ "${#a_output_pass[@]}" -gt 0 ]; then
      echo ""
      echo "  - Correctly set:"
      printf '%s\n' "${a_output_pass[@]}"
    fi
  fi
}
BASH
    ],

    // --- 5.3.3.2.6 Ensure password dictionary check is enabled ---
    [
        'id' => '5.3.3.2.6', 'title' => 'Ensure password dictionary check is enabled', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  PWQUALITY_CONF_DIR="/etc/security/pwquality.conf.d"
  PWQUALITY_CONF_FILE="/etc/security/pwquality.conf"
  PAM_COMMON_PASSWORD_FILE="/etc/pam.d/common-password"

  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL"

  if grep -Psiq '^\h*dictcheck\h*=\h*0\b' "$PWQUALITY_CONF_FILE" "$PWQUALITY_CONF_DIR"/*.conf 2>/dev/null; then
    FOUND_DICTCHECK_DISABLED_CONF=$(grep -Pi -- '^\h*dictcheck\h*=\h*0\b' "$PWQUALITY_CONF_FILE" "$PWQUALITY_CONF_DIR"/*.conf 2>/dev/null)
    a_output_fail+=(" - 'dictcheck' is explicitly disabled (set to 0) in pwquality configuration: '$FOUND_DICTCHECK_DISABLED_CONF'.")
  else
    a_output_pass+=(" - 'dictcheck' is not explicitly disabled (set to 0) in pwquality configuration files.")
  fi

  if [ ! -f "$PAM_COMMON_PASSWORD_FILE" ]; then
    a_output_fail+=(" - PAM common-password file '$PAM_COMMON_PASSWORD_FILE' does not exist.")
  else
    if grep -Psiq '^\h*password\h+(?:requisite|required|sufficient)\h+pam_pwquality\.so\h+([^#\n\r]+\h+)?dictcheck\h*=\h*0\b' "$PAM_COMMON_PASSWORD_FILE"; then
      FOUND_PAM_DICTCHECK_DISABLED_ARG=$(grep -Pi -- '^\h*password\h+(requisite|required|sufficient)\h+pam_pwquality\.so\h+([^#\n\r]+\h+)?dictcheck\h*=\h*0\b' "$PAM_COMMON_PASSWORD_FILE")
      a_output_fail+=(" - '$PAM_COMMON_PASSWORD_FILE' contains 'dictcheck' argument set to 0 (disabled): '$FOUND_PAM_DICTCHECK_DISABLED_ARG'.")
    else
      a_output_pass+=(" - '$PAM_COMMON_PASSWORD_FILE' does not set 'dictcheck' to 0 (disabled) as a module argument.")
    fi
  fi

  echo "- Audit Result:"
  if [ "${#a_output_fail[@]}" -eq 0 ]; then
    echo "  ** PASS **"
    AUDIT_OVERALL_STATUS="PASS"
    printf '%s\n' "${a_output_pass[@]}"
  else
    echo "  ** FAIL **"
    printf '%s\n' "${a_output_fail[@]}"
    if [ "${#a_output_pass[@]}" -gt 0 ]; then
      echo ""
      echo "  - Correctly set:"
      printf '%s\n' "${a_output_pass[@]}"
    fi
  fi
}
BASH
    ],

    // --- 5.3.3.2.7 Ensure password quality checking is enforced ---
    [
        'id' => '5.3.3.2.7', 'title' => 'Ensure password quality checking is enforced', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  PWQUALITY_CONF_DIR="/etc/security/pwquality.conf.d"
  PWQUALITY_CONF_FILE="/etc/security/pwquality.conf"
  PAM_COMMON_PASSWORD_FILE="/etc/pam.d/common-password"

  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL" 

  if grep -PHsiq '^\h*enforcing\h*=\h*0\b' "$PWQUALITY_CONF_FILE" "$PWQUALITY_CONF_DIR"/*.conf 2>/dev/null; then
    FOUND_ENFORCING_DISABLED_CONF=$(grep -PHsi -- '^\h*enforcing\h*=\h*0\b' "$PWQUALITY_CONF_FILE" "$PWQUALITY_CONF_DIR"/*.conf 2>/dev/null)
    a_output_fail+=(" - 'enforcing' is explicitly disabled (set to 0) in pwquality configuration: '$FOUND_ENFORCING_DISABLED_CONF'.")
  else
    a_output_pass+=(" - 'enforcing' is not explicitly disabled (set to 0) in pwquality configuration files.")
  fi

  if [ ! -f "$PAM_COMMON_PASSWORD_FILE" ]; then
    a_output_fail+=(" - PAM common-password file '$PAM_COMMON_PASSWORD_FILE' does not exist.")
  else
    if grep -PHsiq '^\h*password\h+[^#\n\r]+\h+pam_pwquality\.so\h+([^#\n\r]+\h+)?enforcing=0\b' "$PAM_COMMON_PASSWORD_FILE"; then
      FOUND_PAM_ENFORCING_DISABLED_ARG=$(grep -PHsi -- '^\h*password\h+[^#\n\r]+\h+pam_pwquality\.so\h+([^#\n\r]+\h+)?enforcing=0\b' "$PAM_COMMON_PASSWORD_FILE")
      a_output_fail+=(" - '$PAM_COMMON_PASSWORD_FILE' contains 'enforcing' argument set to 0 (disabled): '$FOUND_PAM_ENFORCING_DISABLED_ARG'.")
    else
      a_output_pass+=(" - '$PAM_COMMON_PASSWORD_FILE' does not set 'enforcing' to 0 (disabled) as a module argument.")
    fi
  fi

  echo "- Audit Result:"
  if [ "${#a_output_fail[@]}" -eq 0 ]; then
    echo "  ** PASS **"
    AUDIT_OVERALL_STATUS="PASS"
    printf '%s\n' "${a_output_pass[@]}"
  else
    echo "  ** FAIL **"
    printf '%s\n' "${a_output_fail[@]}"
    if [ "${#a_output_pass[@]}" -gt 0 ]; then
      echo ""
      echo "  - Correctly set:"
      printf '%s\n' "${a_output_pass[@]}"
    fi
  fi
}
BASH
    ],

    // --- 5.3.3.2.8 Ensure password quality is enforced for the root user ---
    [
        'id' => '5.3.3.2.8', 'title' => 'Ensure password quality is enforced for the root user', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  PWQUALITY_CONF_DIR="/etc/security/pwquality.conf.d"
  PWQUALITY_CONF_FILE="/etc/security/pwquality.conf"

  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL" 

  if grep -Psiq '^\h*enforce_for_root\b' "$PWQUALITY_CONF_FILE" "$PWQUALITY_CONF_DIR"/*.conf 2>/dev/null; then
    LATEST_ENFORCE_ROOT_FILE=""

    for conf_file in "$PWQUALITY_CONF_DIR"/*.conf; do
        if [[ -f "$conf_file" ]]; then
            if grep -Psiq '^\h*enforce_for_root\b' "$conf_file"; then
                LATEST_ENFORCE_ROOT_FILE="$conf_file"
            fi
        fi
    done

    if [[ -z "$LATEST_ENFORCE_ROOT_FILE" && -f "$PWQUALITY_CONF_FILE" ]]; then
        if grep -Psiq '^\h*enforce_for_root\b' "$PWQUALITY_CONF_FILE"; then
            LATEST_ENFORCE_ROOT_FILE="$PWQUALITY_CONF_FILE"
        fi
    fi

    if [[ -n "$LATEST_ENFORCE_ROOT_FILE" ]]; then
      a_output_pass+=(" - 'enforce_for_root' is enabled in pwquality configuration file: '$LATEST_ENFORCE_ROOT_FILE'.")
    else
      a_output_fail+=(" - 'enforce_for_root' is not explicitly enabled in any pwquality configuration file despite initial match (precedence issue).")
    fi
  else
    a_output_fail+=(" - 'enforce_for_root' option is not enabled in any pwquality configuration file.")
  fi

  echo "- Audit Result:"
  if [ "${#a_output_fail[@]}" -eq 0 ]; then
    echo "  ** PASS **"
    AUDIT_OVERALL_STATUS="PASS"
    printf '%s\n' "${a_output_pass[@]}"
  else
    echo "  ** FAIL **"
    printf '%s\n' "${a_output_fail[@]}"
    if [ "${#a_output_pass[@]}" -gt 0 ]; then
      echo ""
      echo "  - Correctly set:"
      printf '%s\n' "${a_output_pass[@]}"
    fi
  fi
}
BASH
    ],

    // --- 5.3.3.3 Configure pam_pwhistory module ---
    [ 'id' => '5.3.3', 'title' => 'Configure pam_pwhistory module', 'type' => 'header' ],

    // --- 5.3.3.3.1 Ensure password history remember is configured ---
    [
        'id' => '5.3.3.3.1', 'title' => 'Ensure password history remember is configured', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  PAM_COMMON_PASSWORD_FILE="/etc/pam.d/common-password"

  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL" 

  if [ ! -f "$PAM_COMMON_PASSWORD_FILE" ]; then
    a_output_fail+=(" - PAM common-password file '$PAM_COMMON_PASSWORD_FILE' does not exist.")
  else
    PAM_PWHISTORY_LINE=$(grep -Psi -- '^\h*password\h+[^#\n\r]+\h+pam_pwhistory\.so\h+([^#\n\r]+\h+)?remember=\d+\b' "$PAM_COMMON_PASSWORD_FILE")

    if [[ -n "$PAM_PWHISTORY_LINE" ]]; then
      REMEMBER_VALUE=$(echo "$PAM_PWHISTORY_LINE" | grep -Po 'remember=\K\d+' | head -n 1) 
      
      if [[ -n "$REMEMBER_VALUE" && "$REMEMBER_VALUE" -ge 24 ]]; then
        a_output_pass+=(" - '$PAM_COMMON_PASSWORD_FILE' 'pam_pwhistory.so' line includes 'remember=$REMEMBER_VALUE', which is 24 or more (correct).")
      else
        a_output_fail+=(" - '$PAM_COMMON_PASSWORD_FILE' 'pam_pwhistory.so' line includes 'remember=$REMEMBER_VALUE', which is less than 24 or not properly configured for comparison.")
      fi
    else
      a_output_fail+=(" - '$PAM_COMMON_PASSWORD_FILE' does not contain a 'pam_pwhistory.so' line with a 'remember' argument, or it's commented out.")
    fi
  fi

  echo "- Audit Result:"
  if [ "${#a_output_fail[@]}" -eq 0 ]; then
    echo "  ** PASS **"
    AUDIT_OVERALL_STATUS="PASS"
    printf '%s\n' "${a_output_pass[@]}"
  else
    echo "  ** FAIL **"
    printf '%s\n' "${a_output_fail[@]}"
    if [ "${#a_output_pass[@]}" -gt 0 ]; then
      echo ""
      echo "  - Correctly set:"
      printf '%s\n' "${a_output_pass[@]}"
    fi
  fi
}
BASH
    ],

    // --- 5.3.3.3.2 Ensure password history is enforced for the root user ---
    [
        'id' => '5.3.3.3.2', 'title' => 'Ensure password history is enforced for the root user', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  PAM_COMMON_PASSWORD_FILE="/etc/pam.d/common-password"

  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL" 

  if [ ! -f "$PAM_COMMON_PASSWORD_FILE" ]; then
    a_output_fail+=(" - PAM common-password file '$PAM_COMMON_PASSWORD_FILE' doesn't exist.")
  else
    if grep -Psiq '^\h*password\h+[^#\n\r]+\h+pam_pwhistory\.so\h+([^#\n\r]+\h+)?enforce_for_root\b' "$PAM_COMMON_PASSWORD_FILE"; then
      FOUND_ENFORCE_FOR_ROOT=$(grep -Psi -- '^\h*password\h+[^#\n\r]+\h+pam_pwhistory\.so\h+([^#\n\r]+\h+)?enforce_for_root\b' "$PAM_COMMON_PASSWORD_FILE")
      a_output_pass+=(" - '$PAM_COMMON_PASSWORD_FILE' 'pam_pwhistory.so' line includes 'enforce_for_root' (correct). Found: '$FOUND_ENFORCE_FOR_ROOT'.")
    else
      a_output_fail+=(" - '$PAM_COMMON_PASSWORD_FILE' 'pam_pwhistory.so' line doesn't include 'enforce_for_root', or it's commented out.")
    fi
  fi

  echo "- Audit Result:"
  if [ "${#a_output_fail[@]}" -eq 0 ]; then
    echo "  ** PASS **"
    AUDIT_OVERALL_STATUS="PASS"
    printf '%s\n' "${a_output_pass[@]}"
  else
    echo "  ** FAIL **"
    printf '%s\n' "${a_output_fail[@]}"
    if [ "${#a_output_pass[@]}" -gt 0 ]; then
      echo ""
      echo "  - Correctly set:"
      printf '%s\n' "${a_output_pass[@]}"
    fi
  fi
}
BASH
    ],

    // --- 5.3.3.3.3 Ensure pam_pwhistory includes use_authtok ---
    [
        'id' => '5.3.3.3.3', 'title' => 'Ensure pam_pwhistory includes use_authtok', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  PAM_COMMON_PASSWORD_FILE="/etc/pam.d/common-password"

  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL"

  if [ ! -f "$PAM_COMMON_PASSWORD_FILE" ]; then
    a_output_fail+=(" - PAM common-password file '$PAM_COMMON_PASSWORD_FILE' does not exist.")
  else
    if grep -Psiq '^\h*password\h+[^#\n\r]+\h+pam_pwhistory\.so\h+([^#\n\r]+\h+)?use_authtok\b' "$PAM_COMMON_PASSWORD_FILE"; then
      FOUND_USE_AUTHTOK=$(grep -Psi -- '^\h*password\h+[^#\n\r]+\h+pam_pwhistory\.so\h+([^#\n\r]+\h+)?use_authtok\b' "$PAM_COMMON_PASSWORD_FILE")
      a_output_pass+=(" - '$PAM_COMMON_PASSWORD_FILE' 'pam_pwhistory.so' line includes 'use_authtok' (correct). Found: '$FOUND_USE_AUTHTOK'.")
    else
      a_output_fail+=(" - '$PAM_COMMON_PASSWORD_FILE' 'pam_pwhistory.so' line does not include 'use_authtok', or it's commented out.")
    fi
  fi

  echo "- Audit Result:"
  if [ "${#a_output_fail[@]}" -eq 0 ]; then
    echo "  ** PASS **"
    AUDIT_OVERALL_STATUS="PASS"
    printf '%s\n' "${a_output_pass[@]}"
  else
    echo "  ** FAIL **"
    printf '%s\n' "${a_output_fail[@]}"
    if [ "${#a_output_pass[@]}" -gt 0 ]; then
      echo ""
      echo "  - Correctly set:"
      printf '%s\n' "${a_output_pass[@]}"
    fi
  fi
}
BASH
    ],

    // --- 5.3.3.4 Configure pam_unix module ---
    [ 'id' => '5.3.3.4', 'title' => 'Configure pam_unix module', 'type' => 'header' ],

    // --- 5.3.3.4.1 Ensure pam_unix does not include nullok ---
    [
        'id' => '5.3.3.4.1', 'title' => 'Ensure pam_unix does not include nullok', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  PAM_FILES_TO_CHECK=(
    "/etc/pam.d/common-password"
    "/etc/pam.d/common-auth"
    "/etc/pam.d/common-account"
    "/etc/pam.d/common-session"
    "/etc/pam.d/common-session-noninteractive"
  )

  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="PASS" 

  for pam_file in "${PAM_FILES_TO_CHECK[@]}"; do
    if [ ! -f "$pam_file" ]; then
      a_output_fail+=(" - PAM file '$pam_file' does not exist. Cannot verify 'nullok' setting for this file.")
      AUDIT_OVERALL_STATUS="FAIL"
      continue
    fi

    if grep -PHsiq '^\h*[^#\n\r]+\h+pam_unix\.so\h+([^#\n\r]+\h+)?nullok\b' "$pam_file"; then
      FOUND_NULLOK_ARG=$(grep -PHsi -- '^\h*[^#\n\r]+\h+pam_unix\.so\h+([^#\n\r]+\h+)?nullok\b' "$pam_file")
      a_output_fail+=(" - PAM file '$pam_file' contains 'nullok' argument on 'pam_unix.so' line: '$FOUND_NULLOK_ARG'.")
      AUDIT_OVERALL_STATUS="FAIL"
    else
      a_output_pass+=(" - PAM file '$pam_file' does not contain 'nullok' argument on 'pam_unix.so' line (correct).")
    fi
  done

  echo "- Audit Result:"
  if [ "$AUDIT_OVERALL_STATUS" == "PASS" ]; then
    echo "  ** PASS **"
    printf '%s\n' "${a_output_pass[@]}"
  else
    echo "  ** FAIL **"
    printf '%s\n' "${a_output_fail[@]}"
    if [ "${#a_output_pass[@]}" -gt 0 ]; then
      echo ""
      echo "  - Correctly set:"
      printf '%s\n' "${a_output_pass[@]}"
    fi
  fi
}
BASH
    ],

    // --- 5.3.3.4.2 Ensure pam_unix does not include remember ---
    [
        'id' => '5.3.3.4.2', 'title' => 'Ensure pam_unix does not include remember', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  PAM_FILES_TO_CHECK=(
    "/etc/pam.d/common-password"
    "/etc/pam.d/common-auth"
    "/etc/pam.d/common-account"
    "/etc/pam.d/common-session"
    "/etc/pam.d/common-session-noninteractive"
  )

  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="PASS" # Default to PASS, change to FAIL if any check fails

  # Audit: Verify that the 'remember' argument is not set on the pam_unix.so module.
  for pam_file in "${PAM_FILES_TO_CHECK[@]}"; do
    if [ ! -f "$pam_file" ]; then
      a_output_fail+=(" - PAM file '$pam_file' doesn't exist. Can't verify 'remember' setting for this file.")
      AUDIT_OVERALL_STATUS="FAIL"
      continue
    fi

    # The regex matches lines that are NOT commented out (missing '#') AND contain 'pam_unix.so' followed by 'remember=<digits>'
    if grep -PHsiq '^\h*[^#\n\r]+\h+pam_unix\.so\h+([^#\n\r]+\h+)?remember=\d+\b' "$pam_file"; then
      FOUND_REMEMBER_ARG=$(grep -PHsi -- '^\h*[^#\n\r]+\h+pam_unix\.so\h+([^#\n\r]+\h+)?remember=\d+\b' "$pam_file")
      a_output_fail+=(" - PAM file '$pam_file' contains 'remember' argument on 'pam_unix.so' line: '$FOUND_REMEMBER_ARG'.")
      AUDIT_OVERALL_STATUS="FAIL"
    else
      a_output_pass+=(" - PAM file '$pam_file' doesn't contain 'remember' argument on 'pam_unix.so' line (correct).")
    fi
  done

  echo "- Audit Result:"
  if [ "$AUDIT_OVERALL_STATUS" == "PASS" ]; then
    echo "  ** PASS **"
    printf '%s\n' "${a_output_pass[@]}"
  else
    echo "  ** FAIL **"
    printf '%s\n' "${a_output_fail[@]}"
    if [ "${#a_output_pass[@]}" -gt 0 ]; then
      echo ""
      echo "  - Correctly set:"
      printf '%s\n' "${a_output_pass[@]}"
    fi
  fi
}
BASH
    ],

    // --- 5.3.3.4.3 Ensure pam_unix includes a strong password hashing algorithm ---
    [
        'id' => '5.3.3.4.3', 'title' => 'Ensure pam_unix includes a strong password hashing algorithm', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  PAM_COMMON_PASSWORD_FILE="/etc/pam.d/common-password"

  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL" 

  if [ ! -f "$PAM_COMMON_PASSWORD_PASSWORD" ]; then
    a_output_fail+=(" - PAM common-password file '$PAM_COMMON_PASSWORD_FILE' doesn't exist.")
  else
    if grep -PHq '^\h*password\h+([^#\n\r]+)\h+pam_unix\.so\h+([^#\n\r]+\h+)?(sha512|yescrypt)\b' "$PAM_COMMON_PASSWORD_FILE"; then
      FOUND_ALGORITHM_LINE=$(grep -PH -- '^\h*password\h+([^#\n\r]+)\h+pam_unix\.so\h+([^#\n\r]+\h+)?(sha512|yescrypt)\b' "$PAM_COMMON_PASSWORD_FILE")
      a_output_pass+=(" - PAM file '$PAM_COMMON_PASSWORD_FILE' 'pam_unix.so' line includes a strong hashing algorithm (sha512 or yescrypt): '$FOUND_ALGORITHM_LINE'.")
    else
      a_output_fail+=(" - PAM file '$PAM_COMMON_PASSWORD_FILE' 'pam_unix.so' line doesn't include 'sha512' or 'yescrypt' hashing algorithm, or is commented out.")
    fi
  fi

  echo "- Audit Result:"
  if [ "${#a_output_fail[@]}" -eq 0 ]; then
    echo "  ** PASS **"
    AUDIT_OVERALL_STATUS="PASS"
    printf '%s\n' "${a_output_pass[@]}"
  else
    echo "  ** FAIL **"
    printf '%s\n' "${a_output_fail[@]}"
    if [ "${#a_output_pass[@]}" -gt 0 ]; then
      echo ""
      echo "  - Correctly set:"
      printf '%s\n' "${a_output_pass[@]}"
    fi
  fi
}
BASH
    ],

    // --- 5.3.3.4.4 Ensure pam_unix includes use_authtok ---
    [
        'id' => '5.3.3.4.4', 'title' => 'Ensure pam_unix includes use_authtok', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  PAM_COMMON_PASSWORD_FILE="/etc/pam.d/common-password"

  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL" 

  if [ ! -f "$PAM_COMMON_PASSWORD_FILE" ]; then
    a_output_fail+=(" - PAM common-password file '$PAM_COMMON_PASSWORD_FILE' does not exist.")
  else
    if grep -PHq '^\h*password\h+([^#\n\r]+)\h+pam_unix\.so\h+([^#\n\r]+\h+)?use_authtok\b' "$PAM_COMMON_PASSWORD_FILE"; then
      FOUND_USE_AUTHTOK_LINE=$(grep -PH -- '^\h*password\h+([^#\n\r]+)\h+pam_unix\.so\h+([^#\n\r]+\h+)?use_authtok\b' "$PAM_COMMON_PASSWORD_FILE")
      a_output_pass+=(" - PAM file '$PAM_COMMON_PASSWORD_FILE' 'pam_unix.so' line includes 'use_authtok' (correct). Found: '$FOUND_USE_AUTHTOK_LINE'.")
    else
      a_output_fail+=(" - PAM file '$PAM_COMMON_PASSWORD_FILE' 'pam_unix.so' line does not include 'use_authtok', or it's commented out.")
    fi
  fi

  echo "- Audit Result:"
  if [ "${#a_output_fail[@]}" -eq 0 ]; then
    echo "  ** PASS **"
    AUDIT_OVERALL_STATUS="PASS"
    printf '%s\n' "${a_output_pass[@]}"
  else
    echo "  ** FAIL **"
    printf '%s\n' "${a_output_fail[@]}"
    if [ "${#a_output_pass[@]}" -gt 0 ]; then
      echo ""
      echo "  - Correctly set:"
      printf '%s\n' "${a_output_pass[@]}"
    fi
  fi
}
BASH
    ],

    // --- 5.4 User Accounts and Environment ---
    [ 'id' => '5.4', 'title' => ' User Accounts and Environment', 'type' => 'header' ],

    // --- 5.4.1 Configure shadow password suite parameters ---
    [ 'id' => '5.4.1', 'title' => 'Configure shadow password suite parameters', 'type' => 'header' ],

    // --- 5.4.1.1 Ensure password expiration is configured ---
    [
        'id' => '5.4.1.1', 'title' => 'Ensure password expiration is configured', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  LOGIN_DEFS_FILE="/etc/login.defs"
  SHADOW_FILE="/etc/shadow"

  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL" 
  
  if [ ! -f "$LOGIN_DEFS_FILE" ]; then
    a_output_fail+=(" - Configuration file '$LOGIN_DEFS_FILE' does not exist.")
  else
    LOGIN_DEFS_PASS_MAX_DAYS=$(grep -Pi -- '^\h*PASS_MAX_DAYS\h+\d+\b' "$LOGIN_DEFS_FILE" | awk '{print $2}' | tr -d '[:space:]')

    if [[ -n "$LOGIN_DEFS_PASS_MAX_DAYS" ]]; then
      if (( LOGIN_DEFS_PASS_MAX_DAYS >= 1 && LOGIN_DEFS_PASS_MAX_DAYS <= 365 )); then
        a_output_pass+=(" - '$LOGIN_DEFS_FILE' 'PASS_MAX_DAYS' is '$LOGIN_DEFS_PASS_MAX_DAYS' (correct, 1-365 days).")
      else
        a_output_fail+=(" - '$LOGIN_DEFS_FILE' 'PASS_MAX_DAYS' is '$LOGIN_DEFS_PASS_MAX_DAYS' (incorrect, outside 1-365 days).")
      fi
    else
      a_output_fail+=(" - '$LOGIN_DEFS_FILE' 'PASS_MAX_DAYS' setting is not found or is commented out.")
    fi
  fi

  if [ ! -f "$SHADOW_FILE" ]; then
    a_output_fail+=(" - Shadow file '$SHADOW_FILE' does not exist.")
  else
    SHADOW_PASS_MAX_DAYS_ISSUE=$(awk -F: '($2~/^\$.+\$/) {if($5 > 365 || $5 < 1)print "User: " $1 " PASS_MAX_DAYS: " $5}' "$SHADOW_FILE")

    if [[ -z "$SHADOW_PASS_MAX_DAYS_ISSUE" ]]; then
      a_output_pass+=(" - All /etc/shadow passwords 'PASS_MAX_DAYS' are configured correctly (1-365 days).")
    else
      a_output_fail+=(" - Some /etc/shadow passwords have 'PASS_MAX_DAYS' configured incorrectly (outside 1-365 days):")
      while IFS= read -r line; do
        a_output_fail+=("   $line")
      done <<< "$SHADOW_PASS_MAX_DAYS_ISSUE"
    fi
  fi

  echo "- Audit Result:"
  if [ "${#a_output_fail[@]}" -eq 0 ]; then
    echo "  ** PASS **"
    AUDIT_OVERALL_STATUS="PASS"
    printf '%s\n' "${a_output_pass[@]}"
  else
    echo "  ** FAIL **"
    printf '%s\n' "${a_output_fail[@]}"
    if [ "${#a_output_pass[@]}" -gt 0 ]; then
      echo ""
      echo "  - Correctly set:"
      printf '%s\n' "${a_output_pass[@]}"
    fi
  fi
}
BASH
    ],

    // --- 5.4.1.2 Ensure minimum password days is configured ---
    [
        'id' => '5.4.1.2', 'title' => 'Ensure minimum password days is configured', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  LOGIN_DEFS_FILE="/etc/login.defs"
  SHADOW_FILE="/etc/shadow"

  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL" 

  if [ ! -f "$LOGIN_DEFS_FILE" ]; then
    a_output_fail+=(" - Configuration file '$LOGIN_DEFS_FILE' doesn't exist.")
  else
    LOGIN_DEFS_PASS_MIN_DAYS=$(grep -Pi -- '^\h*PASS_MIN_DAYS\h+\d+\b' "$LOGIN_DEFS_FILE" | awk '{print $2}' | tr -d '[:space:]')

    if [[ -n "$LOGIN_DEFS_PASS_MIN_DAYS" ]]; then
      if (( LOGIN_DEFS_PASS_MIN_DAYS > 0 )); then
        a_output_pass+=(" - '$LOGIN_DEFS_FILE' 'PASS_MIN_DAYS' is '$LOGIN_DEFS_PASS_MIN_DAYS' (correct, > 0 days).")
      else
        a_output_fail+=(" - '$LOGIN_DEFS_FILE' 'PASS_MIN_DAYS' is '$LOGIN_DEFS_PASS_MIN_DAYS' (incorrect, not > 0 days).")
      fi
    else
      a_output_fail+=(" - '$LOGIN_DEFS_FILE' 'PASS_MIN_DAYS' setting isn't found or is commented out.")
    fi
  fi

  if [ ! -f "$SHADOW_FILE" ]; then
    a_output_fail+=(" - Shadow file '$SHADOW_FILE' doesn't exist.")
  else
    SHADOW_PASS_MIN_DAYS_ISSUE=$(awk -F: '($2~/^\$.+\$/) {if($4 < 1)print "User: " $1 " PASS_MIN_DAYS: " $4}' "$SHADOW_FILE")

    if [[ -z "$SHADOW_PASS_MIN_DAYS_ISSUE" ]]; then
      a_output_pass+=(" - All /etc/shadow passwords have 'PASS_MIN_DAYS' correctly configured (> 0 days).")
    else
      a_output_fail+=(" - Some /etc/shadow passwords have 'PASS_MIN_DAYS' configured incorrectly (< 1 day):")
      while IFS= read -r line; do
        a_output_fail+=("   $line")
      done <<< "$SHADOW_PASS_MIN_DAYS_ISSUE"
    fi
  fi

  echo "- Audit Result:"
  if [ "${#a_output_fail[@]}" -eq 0 ]; then
    echo "  ** PASS **"
    AUDIT_OVERALL_STATUS="PASS"
    printf '%s\n' "${a_output_pass[@]}"
  else
    echo "  ** FAIL **"
    printf '%s\n' "${a_output_fail[@]}"
    if [ "${#a_output_pass[@]}" -gt 0 ]; then
      echo ""
      echo "  - Correctly set:"
      printf '%s\n' "${a_output_pass[@]}"
    fi
  fi
}
BASH
    ],

    // --- 5.4.1.3 Ensure password expiration warning days is configured---
    [
        'id' => '5.4.1.3', 'title' => 'Ensure password expiration warning days is configured', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  LOGIN_DEFS_FILE="/etc/login.defs"
  SHADOW_FILE="/etc/shadow"

  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL" 

  if [ ! -f "$LOGIN_DEFS_FILE" ]; then
    a_output_fail+=(" - Configuration file '$LOGIN_DEFS_FILE' doesn't exist.")
  else
    LOGIN_DEFS_PASS_WARN_AGE=$(grep -Pi -- '^\h*PASS_WARN_AGE\h+\d+\b' "$LOGIN_DEFS_FILE" | awk '{print $2}' | tr -d '[:space:]')

    if [[ -n "$LOGIN_DEFS_PASS_WARN_AGE" ]]; then
      if (( LOGIN_DEFS_PASS_WARN_AGE >= 7 )); then
        a_output_pass+=(" - '$LOGIN_DEFS_FILE' 'PASS_WARN_AGE' is '$LOGIN_DEFS_PASS_WARN_AGE' (correct, >= 7 days).")
      else
        a_output_fail+=(" - '$LOGIN_DEFS_FILE' 'PASS_WARN_AGE' is '$LOGIN_DEFS_PASS_WARN_AGE' (incorrect, < 7 days).")
      fi
    else
      a_output_fail+=(" - '$LOGIN_DEFS_FILE' 'PASS_WARN_AGE' setting isn't found or is commented out (no warning will be provided).")
    fi
  fi

  if [ ! -f "$SHADOW_FILE" ]; then
    a_output_fail+=(" - Shadow file '$SHADOW_FILE' doesn't exist.")
  else
    SHADOW_PASS_WARN_AGE_ISSUE=$(awk -F: '($2~/^\$.+\$/) {if($6 < 7)print "User: " $1 " PASS_WARN_AGE: " $6}' "$SHADOW_FILE")

    if [[ -z "$SHADOW_PASS_WARN_AGE_ISSUE" ]]; then
      a_output_pass+=(" - All /etc/shadow passwords have 'PASS_WARN_AGE' correctly configured (>= 7 days).")
    else
      a_output_fail+=(" - Some /etc/shadow passwords have 'PASS_WARN_AGE' configured incorrectly (< 7 days):")
      while IFS= read -r line; do
        a_output_fail+=("   $line")
      done <<< "$SHADOW_PASS_WARN_AGE_ISSUE"
    fi
  fi

  echo "- Audit Result:"
  if [ "${#a_output_fail[@]}" -eq 0 ]; then
    echo "  ** PASS **"
    AUDIT_OVERALL_STATUS="PASS"
    printf '%s\n' "${a_output_pass[@]}"
  else
    echo "  ** FAIL **"
    printf '%s\n' "${a_output_fail[@]}"
    if [ "${#a_output_pass[@]}" -gt 0 ]; then
      echo ""
      echo "  - Correctly set:"
      printf '%s\n' "${a_output_pass[@]}"
    fi
  fi
}
BASH
    ],

    // --- 5.4.1.4 Ensure strong password hashing algorithm is configured ---
    [
        'id' => '5.4.1.4', 'title' => 'Ensure strong password hashing algorithm is configured', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  LOGIN_DEFS_FILE="/etc/login.defs"

  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL" 

  if [ ! -f "$LOGIN_DEFS_FILE" ]; then
    a_output_fail+=(" - Configuration file '$LOGIN_DEFS_FILE' does not exist.")
  else
    if grep -Piq '^\h*ENCRYPT_METHOD\h+(SHA512|yescrypt)\b' "$LOGIN_DEFS_FILE"; then
      FOUND_ENCRYPT_METHOD=$(grep -Pi -- '^\h*ENCRYPT_METHOD\h+(SHA512|yescrypt)\b' "$LOGIN_DEFS_FILE")
      a_output_pass+=(" - '$LOGIN_DEFS_FILE' 'ENCRYPT_METHOD' is set to a strong hashing algorithm: '$FOUND_ENCRYPT_METHOD' (correct).")
    else
      CURRENT_ENCRYPT_METHOD=$(grep -Pi -- '^\h*ENCRYPT_METHOD\h+\S+\b' "$LOGIN_DEFS_FILE" | awk '{print $2}' | tr -d '[:space:]')
      if [[ -n "$CURRENT_ENCRYPT_METHOD" ]]; then
        a_output_fail+=(" - '$LOGIN_DEFS_FILE' 'ENCRYPT_METHOD' is set to '$CURRENT_ENCRYPT_METHOD', which is not 'SHA512' or 'yescrypt' (incorrect).")
      else
        a_output_fail+=(" - '$LOGIN_DEFS_FILE' 'ENCRYPT_METHOD' setting is not found or is commented out.")
      fi
    fi
  fi

  echo "- Audit Result:"
  if [ "${#a_output_fail[@]}" -eq 0 ]; then
    echo "  ** PASS **"
    AUDIT_OVERALL_STATUS="PASS"
    printf '%s\n' "${a_output_pass[@]}"
  else
    echo "  ** FAIL **"
    printf '%s\n' "${a_output_fail[@]}"
    if [ "${#a_output_pass[@]}" -gt 0 ]; then
      echo ""
      echo "  - Correctly set:"
      printf '%s\n' "${a_output_pass[@]}"
    fi
  fi
}
BASH
    ],

    // --- 5.4.1.5 Ensure inactive password lock is configured ---
    [
        'id' => '5.4.1.5', 'title' => 'Ensure inactive password lock is configured', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => true, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  LOGIN_DEFS_FILE="/etc/login.defs" # Useradd -D pulls defaults from here indirectly
  SHADOW_FILE="/etc/shadow"

  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL" 
  
  USERADD_INACTIVE_SETTING=$(useradd -D | grep INACTIVE | awk -F'=' '{print $2}' | tr -d '[:space:]')

  if [[ -n "$USERADD_INACTIVE_SETTING" ]]; then
    if (( USERADD_INACTIVE_SETTING >= 0 && USERADD_INACTIVE_SETTING <= 45 )); then
      a_output_pass+=(" - 'useradd -D' default 'INACTIVE' setting is '$USERADD_INACTIVE_SETTING' (correct, 0-45 days).")
    else
      a_output_fail+=(" - 'useradd -D' default 'INACTIVE' setting is '$USERADD_INACTIVE_SETTING' (incorrect, outside 0-45 days).")
    fi
  else
    a_output_fail+=(" - 'useradd -D' default 'INACTIVE' setting is not found or is outside the required range (e.g., default -1 implies no lock).")
  fi

  if [ ! -f "$SHADOW_FILE" ]; then
    a_output_fail+=(" - Shadow file '$SHADOW_FILE' does not exist.")
  else
    SHADOW_INACTIVE_ISSUE=$(awk -F: '($2~/^\$.+\$/) {if($7 > 45 || $7 < 0)print "User: " $1 " INACTIVE: " $7}' "$SHADOW_FILE")

    if [[ -z "$SHADOW_INACTIVE_ISSUE" ]]; then
      a_output_pass+=(" - All /etc/shadow passwords have 'INACTIVE' correctly configured (0-45 days or -1 which is fine if policy allows explicit disable).")
    else
      a_output_fail+=(" - Some /etc/shadow passwords have 'INACTIVE' configured incorrectly (outside 0-45 days or explicitly -1 if policy dictates enforcement):")
      while IFS= read -r line; do
        a_output_fail+=("   $line")
      done <<< "$SHADOW_INACTIVE_ISSUE"
    fi
  fi

  echo "- Audit Result:"
  if [ "${#a_output_fail[@]}" -eq 0 ]; then
    echo "  ** PASS **"
    AUDIT_OVERALL_STATUS="PASS"
    printf '%s\n' "${a_output_pass[@]}"
  else
    echo "  ** FAIL **"
    printf '%s\n' "${a_output_fail[@]}"
    if [ "${#a_output_pass[@]}" -gt 0 ]; then
      echo ""
      echo "  - Correctly set:"
      printf '%s\n' "${a_output_pass[@]}"
    fi
  fi
}
BASH
    ],

    // --- 5.4.1.6 Ensure all users last password change date is in the past ---
    [
        'id' => '5.4.1.6', 'title' => 'Ensure pam_unix module is enabled', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => true, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  SHADOW_FILE="/etc/shadow"

  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL" 

  if [ ! -f "$SHADOW_FILE" ]; then
    a_output_fail+=(" - Shadow file '$SHADOW_FILE' does not exist. Cannot perform audit.")
  else
    FUTURE_PASSWORD_CHANGE_USERS=""
    while IFS= read -r l_user; do
      LAST_CHANGE_DATE_STR=$(chage --list "$l_user" 2>/dev/null | grep '^Last password change' | cut -d: -f2 | grep -v 'never$' | xargs)

      if [[ -n "$LAST_CHANGE_DATE_STR" ]]; then
        LAST_CHANGE_TIMESTAMP=$(date -d "$LAST_CHANGE_DATE_STR" +%s 2>/dev/null)
        CURRENT_TIMESTAMP=$(date +%s)

        if [[ "$LAST_CHANGE_TIMESTAMP" -gt "$CURRENT_TIMESTAMP" ]]; then
          FUTURE_PASSWORD_CHANGE_USERS+="User: \"$l_user\" last password change was \"$LAST_CHANGE_DATE_STR\"\n"
        fi
      fi
    done < <(awk -F: '$2~/^\$.+\$/{print $1}' "$SHADOW_FILE")

    if [[ -z "$FUTURE_PASSWORD_CHANGE_USERS" ]]; then
      a_output_pass+=(" - All users with passwords have their last password change date in the past (correct).")
    else
      a_output_fail+=(" - The following users have their last password change date set in the future:")
      while IFS= read -r line; do
        a_output_fail+=("   $line")
      done <<< "$FUTURE_PASSWORD_CHANGE_USERS"
    fi
  fi

  echo "- Audit Result:"
  if [ "${#a_output_fail[@]}" -eq 0 ]; then
    echo "  ** PASS **"
    AUDIT_OVERALL_STATUS="PASS"
    printf '%s\n' "${a_output_pass[@]}"
  else
    echo "  ** FAIL **"
    printf '%s\n' "${a_output_fail[@]}"
    if [ "${#a_output_pass[@]}" -gt 0 ]; then
      echo ""
      echo "  - Correctly set:"
      printf '%s\n' "${a_output_pass[@]}"
    fi
  fi
}
BASH
    ],

    // --- 5.4.2 Configure root and system accounts and environment ---
    [ 'id' => '5.4.2', 'title' => 'Configure root and system accounts and environment', 'type' => 'header' ],

    // --- 5.4.2.1 Ensure root is the only UID 0 account ---
    [
        'id' => '5.4.2.1', 'title' => 'Ensure root is the only UID 0 account', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  PASSWD_FILE="/etc/passwd"

  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL" # Default to FAIL

  if [ ! -f "$PASSWD_FILE" ]; then
    a_output_fail+=(" - Password file '$PASSWD_FILE' does not exist.")
  else
    UID_0_USERS=$(awk -F: '($3 == 0) { print $1 }' "$PASSWD_FILE")
    
    NUM_UID_0_USERS=$(echo "$UID_0_USERS" | wc -l)
    
    if [[ "$NUM_UID_0_USERS" -eq 1 && "$UID_0_USERS" == "root" ]]; then
      a_output_pass+=(" - Only 'root' account has UID 0 (correct).")
    else
      a_output_fail+=(" - Multiple or incorrect accounts found with UID 0:")
      while IFS= read -r line; do
        a_output_fail+=("   - $line")
      done <<< "$UID_0_USERS"
    fi
  fi

  echo "- Audit Result:"
  if [ "${#a_output_fail[@]}" -eq 0 ]; then
    echo "  ** PASS **"
    AUDIT_OVERALL_STATUS="PASS"
    printf '%s\n' "${a_output_pass[@]}"
  else
    echo "  ** FAIL **"
    printf '%s\n' "${a_output_fail[@]}"
    if [ "${#a_output_pass[@]}" -gt 0 ]; then
      echo ""
      echo "  - Correctly set:"
      printf '%s\n' "${a_output_pass[@]}"
    fi
  fi
}
BASH
    ],

    // --- 5.4.2.2 Ensure root is the only GID 0 account ---
    [
        'id' => '5.4.2.2', 'title' => 'Ensure root is the only GID 0 account', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  PASSWD_FILE="/etc/passwd"

  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL" 

  if [ ! -f "$PASSWD_FILE" ]; then
    a_output_fail+=(" - Password file '$PASSWD_FILE' does not exist.")
  else
    GID_0_USERS=$(awk -F: '($1 !~ /^(sync|shutdown|halt|operator)/ && $4=="0") {print $1":"$4}' "$PASSWD_FILE")
    
    if [[ "$GID_0_USERS" == "root:0" ]]; then
      a_output_pass+=(" - Only 'root' user has primary GID 0 (correct).")
    else
      a_output_fail+=(" - Other users besides 'root' or no 'root' user found with primary GID 0:")
      while IFS= read -r line; do
        a_output_fail+=("   - $line")
      done <<< "$GID_0_USERS"
    fi
  fi

  echo "- Audit Result:"
  if [ "${#a_output_fail[@]}" -eq 0 ]; then
    echo "  ** PASS **"
    AUDIT_OVERALL_STATUS="PASS"
    printf '%s\n' "${a_output_pass[@]}"
  else
    echo "  ** FAIL **"
    printf '%s\n' "${a_output_fail[@]}"
    if [ "${#a_output_pass[@]}" -gt 0 ]; then
      echo ""
      echo "  - Correctly set:"
      printf '%s\n' "${a_output_pass[@]}"
    fi
  fi
}
BASH
    ],

    // --- 5.4.2.3 Ensure group root is the only GID 0 group---
    [
        'id' => '5.3.2.3', 'title' => 'Ensure group root is the only GID 0 group', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  GROUP_FILE="/etc/group"

  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL" 

  if [ ! -f "$GROUP_FILE" ]; then
    a_output_fail+=(" - Group file '$GROUP_FILE' does not exist.")
  else
    GID_0_GROUPS=$(awk -F: '$3=="0"{print $1":"$3}' "$GROUP_FILE")
    
    if [[ "$GID_0_GROUPS" == "root:0" ]]; then
      a_output_pass+=(" - Only 'root' group has GID 0 (correct).")
    else
      a_output_fail+=(" - Other groups besides 'root' or no 'root' group found with GID 0:")
      while IFS= read -r line; do
        a_output_fail+=("   - $line")
      done <<< "$GID_0_GROUPS"
    fi
  fi

  echo "- Audit Result:"
  if [ "${#a_output_fail[@]}" -eq 0 ]; then
    echo "  ** PASS **"
    AUDIT_OVERALL_STATUS="PASS"
    printf '%s\n' "${a_output_pass[@]}"
  else
    echo "  ** FAIL **"
    printf '%s\n' "${a_output_fail[@]}"
    if [ "${#a_output_pass[@]}" -gt 0 ]; then
      echo ""
      echo "  - Correctly set:"
      printf '%s\n' "${a_output_pass[@]}"
    fi
  fi
}
BASH
    ],

    // --- 5.4.2.4 Ensure root account access is controlled ---
    [
        'id' => '5.4.2.4', 'title' => 'Ensure root account access is controlled', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => true, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL" 

  ROOT_PASSWORD_STATUS=$(passwd -S root 2>/dev/null | awk '$2 ~ /^(P|L)/ {print "User: \"" $1 "\" Password is status: " $2}')

  if [[ -n "$ROOT_PASSWORD_STATUS" ]]; then
    if echo "$ROOT_PASSWORD_STATUS" | grep -qE '^User: "root" Password is status: (P|L)$'; then
      a_output_pass+=(" - Root account access is controlled: '$ROOT_PASSWORD_STATUS' (correct).")
    else
      a_output_fail+=(" - Root account password status is not 'P' (set) or 'L' (locked): '$ROOT_PASSWORD_STATUS'.")
    fi
  else
    a_output_fail+=(" - Unable to determine root account password status using 'passwd -S root'. Command output was empty or unexpected.")
  fi

  echo "- Audit Result:"
  if [ "${#a_output_fail[@]}" -eq 0 ]; then
    echo "  ** PASS **"
    AUDIT_OVERALL_STATUS="PASS"
    printf '%s\n' "${a_output_pass[@]}"
  else
    echo "  ** FAIL **"
    printf '%s\n' "${a_output_fail[@]}"
    if [ "${#a_output_pass[@]}" -gt 0 ]; then
      echo ""
      echo "  - Correctly set:"
      printf '%s\n' "${a_output_pass[@]}"
    fi
  fi
}
BASH
    ],

    // --- 5.4.2.5 Ensure root path integrity ---
    [
        'id' => '5.4.2.5', 'title' => 'Ensure root path integrity', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => true, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
   l_output2=""
   l_pmask="0022"
   l_maxperm="$( printf '%o' $(( 0777 & ~$l_pmask )) )"
   l_root_path="$(sudo -Hiu root env | grep '^PATH' | cut -d= -f2)"
   unset a_path_loc && IFS=":" read -ra a_path_loc <<< "$l_root_path"
   grep -q "::" <<< "$l_root_path" && l_output2="$l_output2\n - root's path contains a empty directory (::)"
   grep -Pq ":\h*$" <<< "$l_root_path" && l_output2="$l_output2\n - root's path contains a trailing (:)"
   grep -Pq '(\h+|:)\.(:|\h*$)' <<< "$l_root_path" && l_output2="$l_output2\n - root's path contains current working directory (.)"
   while read -r l_path; do
      if [ -d "$l_path" ]; then
         while read -r l_fmode l_fown; do
            [ "$l_fown" != "root" ] && l_output2="$l_output2\n - Directory: \"$l_path\" is owned by: \"$l_fown\" should be owned by \"root\""
            [ $(( $l_fmode & $l_pmask )) -gt 0 ] && l_output2="$l_output2\n - Directory: \"$l_path\" is mode: \"$l_fmode\" and should be mode: \"$l_maxperm\" or more restrictive"
         done <<< "$(stat -Lc '%#a %U' "$l_path")"
      else
         l_output2="$l_output2\n - \"$l_path\" is not a directory"
      fi
   done <<< "$(printf "%s\n" "${a_path_loc[@]}")"
   if [ -z "$l_output2" ]; then
      echo -e "\n- Audit Result:\n  *** PASS ***\n - Root's path is correctly configured\n"
   else
      echo -e "\n- Audit Result:\n  ** FAIL **\n - * Reasons for audit failure * :\n$l_output2\n"
   fi
}
BASH
    ],

    // --- 5.4.2.6 Ensure root user umask is configured ---
    [
        'id' => '5.4.2.6', 'title' => 'Ensure root user umask is configured', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  BASH_PROFILE="/root/.bash_profile"
  BASHRC="/root/.bashrc"

  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL" 
  if [ -f "$BASH_PROFILE" ]; then
    if grep -Psiq '^\h*umask\h+(([0-7][0-7][01][0-7]\b|[0-7][0-7][0-7][06]\b)|([0-7][01][0-7]\b|[0-7][0-7][06]\b)|(u=[rwx]{1,3},)?(((g=[rx]?[rx]?w[rx]?[rx]?\b)(,o=[rwx]{1,3})?)|((g=[wrx]{1,3},)?o=[wrx]{1,3}\b)))' "$BASH_PROFILE"; then
      FOUND_BAD_UMASK=$(grep -Psi -- '^\h*umask\h+(([0-7][0-7][01][0-7]\b|[0-7][0-7][0-7][06]\b)|([0-7][01][0-7]\b|[0-7][0-7][06]\b)|(u=[rwx]{1,3},)?(((g=[rx]?[rx]?w[rx]?[rx]?\b)(,o=[rwx]{1,3})?)|((g=[wrx]{1,3},)?o=[wrx]{1,3}\b)))' "$BASH_PROFILE")
      a_output_fail+=(" - Root user's umask in '$BASH_PROFILE' is too permissive: '$FOUND_BAD_UMASK'.")
    else
      a_output_pass+=(" - Root user's umask in '$BASH_PROFILE' is correctly configured (027 or more restrictive).")
    fi
  else
    a_output_pass+=(" - '$BASH_PROFILE' does not exist or does not set umask explicitly (system default or .bashrc may apply).")
  fi

  if [ -f "$BASHRC" ]; then
    if grep -Psiq '^\h*umask\h+(([0-7][0-7][01][0-7]\b|[0-7][0-7][0-7][06]\b)|([0-7][01][0-7]\b|[0-7][0-7][06]\b)|(u=[rwx]{1,3},)?(((g=[rx]?[rx]?w[rx]?[rx]?\b)(,o=[rwx]{1,3})?)|((g=[wrx]{1,3},)?o=[wrx]{1,3}\b)))' "$BASHRC"; then
      FOUND_BAD_UMASK=$(grep -Psi -- '^\h*umask\h+(([0-7][0-7][01][0-7]\b|[0-7][0-7][0-7][06]\b)|([0-7][01][0-7]\b|[0-7][0-7][06]\b)|(u=[rwx]{1,3},)?(((g=[rx]?[rx]?w[rx]?[rx]?\b)(,o=[rwx]{1,3})?)|((g=[wrx]{1,3},)?o=[wrx]{1,3}\b)))' "$BASHRC")
      a_output_fail+=(" - Root user's umask in '$BASHRC' is too permissive: '$FOUND_BAD_UMASK'.")
    else
      a_output_pass+=(" - Root user's umask in '$BASHRC' is correctly configured (027 or more restrictive).")
    fi
  else
    a_output_pass+=(" - '$BASHRC' does not exist or does not set umask explicitly (system default may apply).")
  fi

  echo "- Audit Result:"
  if [ "${#a_output_fail[@]}" -eq 0 ]; then
    echo "  ** PASS **"
    AUDIT_OVERALL_STATUS="PASS"
    printf '%s\n' "${a_output_pass[@]}"
  else
    echo "  ** FAIL **"
    printf '%s\n' "${a_output_fail[@]}"
    if [ "${#a_output_pass[@]}" -gt 0 ]; then
      echo ""
      echo "  - Correctly set:"
      printf '%s\n' "${a_output_pass[@]}"
    fi
  fi
}
BASH
    ],

    // --- 5.4.2.7 Ensure system accounts do not have a valid login shell ---
    [
        'id' => '5.4.2.7', 'title' => 'Ensure system accounts do not have a valid login shell', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  PASSWD_FILE="/etc/passwd"
  SHELLS_FILE="/etc/shells"
  LOGIN_DEFS_FILE="/etc/login.defs"

  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL" 

  if [ ! -f "$PASSWD_FILE" ]; then
    a_output_fail+=(" - Required file '$PASSWD_FILE' does not exist. Cannot perform audit.")
  fi
  if [ ! -f "$SHELLS_FILE" ]; then
    a_output_fail+=(" - Required file '$SHELLS_FILE' does not exist. Cannot determine valid shells.")
  fi
  if [ ! -f "$LOGIN_DEFS_FILE" ]; then
    a_output_fail+=(" - Required file '$LOGIN_DEFS_FILE' does not exist. Cannot determine UID_MIN.")
  fi

  if [ "${#a_output_fail[@]}" -eq 0 ]; then
    l_valid_shells=$(awk -F'/' '$NF != "nologin" {print}' "$SHELLS_FILE" | sed -rn '/^\//{s,/,\\\\/,g;p}' | paste -s -d '|')
    l_valid_shells="^(${l_valid_shells})$"

    UID_MIN=$(awk '/^\s*UID_MIN/{print $2}' "$LOGIN_DEFS_FILE")

    INVALID_SHELL_ACCOUNTS=$(awk -F: -v pat="$l_valid_shells" -v uid_min="$UID_MIN" '
      ($1!~/^(root|halt|sync|shutdown|nfsnobody)$/ && ($3 < uid_min || $3 == 65534) && $(NF) ~ pat) {
        print "Service account: \"" $1 "\" has a valid shell: " $7
      }' "$PASSWD_FILE")

    if [[ -z "$INVALID_SHELL_ACCOUNTS" ]]; then
      a_output_pass+=(" - All system accounts (excluding root, halt, sync, shutdown, nfsnobody) do not have a valid login shell (correct).")
    else
      a_output_fail+=(" - The following system accounts have a valid login shell:")
      while IFS= read -r line; do
        a_output_fail+=("   $line")
      done <<< "$INVALID_SHELL_ACCOUNTS"
    fi
  fi

  echo "- Audit Result:"
  if [ "${#a_output_fail[@]}" -eq 0 ]; then
    echo "  ** PASS **"
    AUDIT_OVERALL_STATUS="PASS"
    printf '%s\n' "${a_output_pass[@]}"
  else
    echo "  ** FAIL **"
    printf '%s\n' "${a_output_fail[@]}"
    if [ "${#a_output_pass[@]}" -gt 0 ]; then
      echo ""
      echo "  - Correctly set:"
      printf '%s\n' "${a_output_pass[@]}"
    fi
  fi
}
BASH
    ],

    // --- 5.4.2.8 Ensure accounts without a valid login shell are locked ---
    [
        'id' => '5.4.2.8', 'title' => 'Ensure accounts without a valid login shell are locked', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  PASSWD_FILE="/etc/passwd"
  SHELLS_FILE="/etc/shells"

  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL" 

  if [ ! -f "$PASSWD_FILE" ]; then
    a_output_fail+=(" - Required file '$PASSWD_FILE' does not exist. Cannot perform audit.")
  fi
  if [ ! -f "$SHELLS_FILE" ]; then
    a_output_fail+=(" - Required file '$SHELLS_FILE' does not exist. Cannot determine valid shells.")
  fi

  if [ "${#a_output_fail[@]}" -eq 0 ]; then
    l_valid_shells=$(awk -F'/' '$NF != "nologin" {print}' "$SHELLS_FILE" | sed -rn '/^\//{s,/,\\\\/,g;p}' | paste -s -d '|')
    l_valid_shells="^(${l_valid_shells})$"

    UNLOCKED_INVALID_SHELL_ACCOUNTS=""
    while IFS= read -r l_user; do
      ACCOUNT_STATUS=$(passwd -S "$l_user" 2>/dev/null | awk '$2 !~ /^L/ {print "Account: \"" $1 "\" does not have a valid login shell and is not locked"}')
      if [[ -n "$ACCOUNT_STATUS" ]]; then
        UNLOCKED_INVALID_SHELL_ACCOUNTS+="$ACCOUNT_STATUS\n"
      fi
    done < <(awk -F: -v pat="$l_valid_shells" '($1 != "root" && $(NF) !~ pat) {print $1}' "$PASSWD_FILE")

    if [[ -z "$UNLOCKED_INVALID_SHELL_ACCOUNTS" ]]; then
      a_output_pass+=(" - All non-root accounts without a valid login shell are locked (correct).")
    else
      a_output_fail+=(" - The following non-root accounts without a valid login shell are NOT locked:")
      while IFS= read -r line; do
        a_output_fail+=("   $line")
      done <<< "$UNLOCKED_INVALID_SHELL_ACCOUNTS"
    fi
  fi

  echo "- Audit Result:"
  if [ "${#a_output_fail[@]}" -eq 0 ]; then
    echo "  ** PASS **"
    AUDIT_OVERALL_STATUS="PASS"
    printf '%s\n' "${a_output_pass[@]}"
  else
    echo "  ** FAIL **"
    printf '%s\n' "${a_output_fail[@]}"
    if [ "${#a_output_pass[@]}" -gt 0 ]; then
      echo ""
      echo "  - Correctly set:"
      printf '%s\n' "${a_output_pass[@]}"
    fi
  fi
}
BASH
    ],

    // --- 5.4.3 Configure user default environment ---
    [ 'id' => '5.4.3', 'title' => 'Configure user default environment', 'type' => 'header' ],

    // --- 5.4.3.1 Ensure nologin is not listed in /etc/shells ---
    [
        'id' => '5.4.3.1', 'title' => 'Ensure nologin is not listed in /etc/shells', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  SHELLS_FILE="/etc/shells"

  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL" 

  if [ ! -f "$SHELLS_FILE" ]; then
    a_output_fail+=(" - The '/etc/shells' file does not exist. Cannot perform audit.")
  else
    if grep -Psic '^\h*([^#\n\r]+)?\/nologin\b' "$SHELLS_FILE" &>/dev/null; then
      FOUND_NOLOGIN_LINE=$(grep -Ps -- '^\h*([^#\n\r]+)?\/nologin\b' "$SHELLS_FILE")
      a_output_fail+=(" - '/etc/shells' file lists 'nologin' as a valid shell: '$FOUND_NOLOGIN_LINE'.")
    else
      a_output_pass+=(" - '/etc/shells' file does not list 'nologin' as a valid shell (correct).")
    fi
  fi

  echo "- Audit Result:"
  if [ "${#a_output_fail[@]}" -eq 0 ]; then
    echo "  ** PASS **"
    AUDIT_OVERALL_STATUS="PASS"
    printf '%s\n' "${a_output_pass[@]}"
  else
    echo "  ** FAIL **"
    printf '%s\n' "${a_output_fail[@]}"
    if [ "${#a_output_pass[@]}" -gt 0 ]; then
      echo ""
      echo "  - Correctly set:"
      printf '%s\n' "${a_output_pass[@]}"
    fi
  fi
}
BASH
    ],

    // --- 5.4.3.2 Ensure default user shell timeout is configured ---
    [
        'id' => '5.4.3.2', 'title' => 'Ensure default user shell timeout is configured', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
  BASH_PROFILE="/etc/profile"
  BASHRC="/etc/bashrc" 
  PROFILE_D_DIR="/etc/profile.d"

  a_output_pass=()
  a_output_fail=()
  AUDIT_OVERALL_STATUS="FAIL" 

  ACTUAL_BASHRC_FILE=""
  if [ -f "$BASHRC" ]; then
    ACTUAL_BASHRC_FILE="$BASHRC"
  elif [ -f "/etc/bash.bashrc" ]; then 
    ACTUAL_BASHRC_FILE="/etc/bash.bashrc"
  fi

  CONFIG_FILES_TO_CHECK=()
  if [ -d "$PROFILE_D_DIR" ]; then
    while IFS= read -r -d $'\0' f; do
      CONFIG_FILES_TO_CHECK+=("$f")
    done < <(find "$PROFILE_D_DIR" -maxdepth 1 -type f -name '*.sh' -print0)
  fi
  CONFIG_FILES_TO_CHECK+=("$BASH_PROFILE")
  if [[ -n "$ACTUAL_BASHRC_FILE" ]]; then
    CONFIG_FILES_TO_CHECK+=("$ACTUAL_BASHRC_FILE")
  fi

  correctly_configured_file=""
  incorrectly_configured_files=""

  correct_tmout_pattern_value="([1-9]|[1-9][0-9]|[1-8][0-9][0-9]|900)"
  correct_tmout_pattern="^\s*([^#]+\s+)?TMOUT=${correct_tmout_pattern_value}\b"
  correct_readonly_pattern="^\s*([^#]+;\s*)?readonly\s+TMOUT(\s+|\s*;|\s*=|=${correct_tmout_pattern_value})\b"
  correct_export_pattern="^\s*([^#]+;\s*)?export\s+TMOUT(\s+|\s*;|\s*=|=${correct_tmout_pattern_value})\b"

  incorrect_tmout_pattern="^\s*([^#]+\s+)?TMOUT=(0|[9][0-9][1-9]|[1-9]\d{3,})\b"

  for f in "${CONFIG_FILES_TO_CHECK[@]}"; do
    if [[ -f "$f" ]]; then
      if grep -Pq "$correct_tmout_pattern" "$f" && \
         grep -Pq "$correct_readonly_pattern" "$f" && \
         grep -Pq "$correct_export_pattern" "$f"; then
        correctly_configured_file="$f"
      fi

      if grep -Pq "$incorrect_tmout_pattern" "$f"; then
        incorrectly_configured_files="$f" 
      fi
    fi
  done

  if [[ -n "$correctly_configured_file" ]] && [[ -z "$incorrectly_configured_files" ]]; then
    a_output_pass+=(" - TMOUT is correctly configured (timeout <= 900, readonly, exported) in '$correctly_configured_file'.")
  else
    if [[ -z "$correctly_configured_file" ]]; then
      a_output_fail+=(" - TMOUT is not configured to be <= 900, readonly, and exported in any checked file.")
    fi
    if [[ -n "$incorrectly_configured_files" ]]; then
      a_output_fail+=(" - TMOUT is incorrectly configured (timeout = 0 or > 900) in '$incorrectly_configured_files'.")
    fi
  fi

  echo "- Audit Result:"
  if [ "${#a_output_fail[@]}" -eq 0 ]; then
    echo "  ** PASS **"
    AUDIT_OVERALL_STATUS="PASS"
    printf '%s\n' "${a_output_pass[@]}"
  else
    echo "  ** FAIL **"
    printf '%s\n' "${a_output_fail[@]}"
    if [ "${#a_output_pass[@]}" -gt 0 ]; then
      echo ""
      echo "  - Correctly set:"
      printf '%s\n' "${a_output_pass[@]}"
    fi
  fi
}
BASH
    ],

    // --- 5.4.3.3 Ensure default user umask is configured---
    [
        'id' => '5.3.3.3', 'title' => 'Ensure default user umask is configured', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
    l_output=""
    l_output2=""

    file_umask_chk() {
        if grep -Psiq -- '^\s*umask\s+(0?[02-7]7|u=[rwx]{0,3},g=[rx]{0,2},o=)' "$l_file"; then
            l_output+="\n - umask is set correctly in \"$l_file\""
        elif grep -Psiq -- '^\s*umask\s+((0[0-6][0-7])|([0-7]{3}))' "$l_file"; then
            l_output2+="\n - umask is incorrectly set in \"$l_file\""
        fi
    }

    while IFS= read -r -d '' l_file; do
        file_umask_chk
    done < <(find /etc/profile.d/ -type f -name '*.sh' -print0)

    for l_file in /etc/profile /etc/bashrc /etc/bash.bashrc /etc/login.defs /etc/default/login; do
        [ -z "$l_output" ] && file_umask_chk
    done

    if [ -z "$l_output" ]; then
        l_file="/etc/pam.d/postlogin"
        if grep -Psiq -- '^\s*session\s+[^#\n\r]+\s+pam_umask\.so\s+([^#\n\r]+\s+)?umask=(0?[02-7]7)\b' "$l_file"; then
            l_output+="\n - umask is set correctly in \"$l_file\""
        elif grep -Psiq -- '^\s*session\s+[^#\n\r]+\s+pam_umask\.so\s+([^#\n\r]+\s+)?umask=([0-7]{3})' "$l_file"; then
            l_output2+="\n - umask is incorrectly set in \"$l_file\""
        fi
    fi

    [[ -z "$l_output" && -z "$l_output2" ]] && l_output2+="\n - umask is not set"

    if [ -z "$l_output2" ]; then
        echo -e "\n- Audit Result:\n  ** PASS **\n - * Correctly configured * :$l_output\n"
    else
        echo -e "\n- Audit Result:\n  ** FAIL **\n - * Reasons for audit failure * :$l_output2"
        [ -n "$l_output" ] && echo -e "\n- * Correctly configured * :$l_output\n"
    fi
}
BASH
    ],
];
