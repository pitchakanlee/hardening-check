<?php
// =============================================================
// == file: CIS_Debian_Linux_12_Benchmark_v1.1.0.pdf
// == section: 3
// =============================================================
return [
    // --- 3 Network ---
    [ 'id' => '3', 'title' => 'Network', 'type' => 'header' ],

    // --- 3.1 Configure Network Devices ---
    [ 'id' => '3.1', 'title' => 'Configure Network Devices', 'type' => 'header' ],

    // --- 3.1.1 Ensure IPv6 status is identified ---
    [
        'id' => '3.1.1', 'title' => 'Ensure IPv6 status is identified', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
    l_output=""

    # Check if IPv6 kernel module is enabled (0 means enabled)
    if grep -Pqs '^\s*0\b' /sys/module/ipv6/parameters/disable; then
        ipv6_kernel_enabled="yes"
    else
        ipv6_kernel_enabled="no"
    fi

    # Check sysctl settings for disabling IPv6
    if sysctl net.ipv6.conf.all.disable_ipv6 2>/dev/null | grep -Pq '=\s*1' && \
       sysctl net.ipv6.conf.default.disable_ipv6 2>/dev/null | grep -Pq '=\s*1'; then
        ipv6_sysctl_disabled="yes"
    else
        ipv6_sysctl_disabled="no"
    fi

    if [[ "$ipv6_kernel_enabled" == "yes" && "$ipv6_sysctl_disabled" == "no" ]]; then
        l_output="- IPv6 is enabled"
    else
        l_output="- IPv6 is not enabled"
    fi

    echo -e "\n$l_output\n"
    echo "Remediation: Enable or disable IPv6 in accordance with system requirements and local site policy"
}

BASH
    ],

    // --- 3.1.2 Ensure wireless interfaces are disabled ---
    [
        'id' => '3.1.2', 'title' => 'Ensure wireless interfaces are disabled', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
   l_output="" l_output2=""

   module_chk() {
      l_loadable="$(modprobe -n -v "$l_mname")"
      if grep -Pq -- '^\h*install \/bin\/(true|false)' <<< "$l_loadable"; then 
         l_output="$l_output\n - module: \"$l_mname\" is not loadable: \"$l_loadable\""
      else
         l_output2="$l_output2\n - module: \"$l_mname\" is loadable: \"$l_loadable\""
      fi

      if ! lsmod | grep "$l_mname" > /dev/null 2>&1; then
         l_output="$l_output\n - module: \"$l_mname\" is not loaded"
      else
         l_output2="$l_output2\n - module: \"$l_mname\" is loaded"
      fi

      if modprobe --showconfig | grep -Pq -- "^\h*blacklist\h+$l_mname\b"; then
         l_output="$l_output\n - module: \"$l_mname\" is deny listed in: \"$(grep -Pl -- "^\h*blacklist\h+$l_mname\b" /etc/modprobe.d/*)\""
      else
         l_output2="$l_output2\n - module: \"$l_mname\" is not deny listed"
      fi
   }

   if [ -n "$(find /sys/class/net/*/ -type d -name wireless)" ]; then
      l_dname=$(for driverdir in $(find /sys/class/net/*/ -type d -name wireless | xargs -0 dirname); do basename "$(readlink -f "$driverdir"/device/driver/module)"; done | sort -u) 
      for l_mname in $l_dname; do
         module_chk
      done
   fi

   if [ -z "$l_output2" ]; then
      echo -e "\n- Audit Result:\n  ** PASS **"
      if [ -z "$l_output" ]; then
         echo -e "\n - System has no wireless NICs installed"
      else
         echo -e "\n$l_output\n"
      fi
   else
      echo -e "\n- Audit Result:\n  ** FAIL **\n - Reason(s) for audit failure:\n$l_output2\n"
      [ -n "$l_output" ] && echo -e "\n- Correctly set:\n$l_output\n"
   fi
}

BASH
    ],

    // --- 3.1.3 Ensure bluetooth services are not in use ---
    [
        'id' => '3.1.3', 'title' => 'Ensure wireless interfaces are disabled', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    a_output_info=()
    PACKAGE_NAME="bluez"
    SERVICE_NAME="bluetooth.service"

    if ! dpkg-query -s "$PACKAGE_NAME" &>/dev/null; then
        a_output_pass+=("  - Package '$PACKAGE_NAME' is not installed.")
    else
        a_output_info+=("  - Package '$PACKAGE_NAME' is installed, checking service status...")

        if systemctl is-enabled "$SERVICE_NAME" 2>/dev/null | grep -q 'enabled'; then
            a_output_fail+=("  - '$SERVICE_NAME' is enabled.")
        else
            a_output_pass+=("  - '$SERVICE_NAME' is not enabled.")
        fi

        if systemctl is-active "$SERVICE_NAME" 2>/dev/null | grep -q 'active'; then
            a_output_fail+=("  - '$SERVICE_NAME' is active.")
        else
            a_output_pass+=("  - '$SERVICE_NAME' is not active.")
        fi

        a_output_info+=("  - Note: If '$PACKAGE_NAME' is required by another package, ensure this is compliant with your site policy.")
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
        printf '%s\n' "If '$PACKAGE_NAME' is not required, run the following commands:"
        printf '%s\n' "# sudo systemctl stop $SERVICE_NAME"
        printf '%s\n' "# sudo apt purge $PACKAGE_NAME"
        printf '%s\n' ""
        printf '%s\n' "- OR - If the package is required as a dependency, run:"
        printf '%s\n' "# sudo systemctl stop $SERVICE_NAME"
        printf '%s\n' "# sudo systemctl mask $SERVICE_NAME"
    fi
}
BASH
    ],

    // --- 3.2 Configure Network Kernel Modules ---
    [ 'id' => '3.2', 'title' => 'Configure Network Kernel Modules', 'type' => 'header' ],

    // --- 3.2.1 Ensure dccp kernel module is not available ---
    [
        'id' => '3.2.1', 'title' => 'Ensure dccp kernel module is not available', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
   a_output=()
   a_output2=()
   a_output3=()
   l_dl=""
   l_mod_name="dccp"
   l_mod_type="net"
   l_mod_path=($(find /lib/modules/ -type d -path "*/kernel/$l_mod_type" 2>/dev/null | sort -u))

   f_module_chk()
   {
      l_dl="y"
      a_showconfig=()
      while IFS= read -r l_showconfig; do
         a_showconfig+=("$l_showconfig")
      done < <(modprobe --showconfig | grep -P -- '\b(install|blacklist)\h+'"${l_mod_chk_name//-/_}"'\b')

      if ! lsmod | grep -q "$l_mod_chk_name"; then
         a_output+=("  - kernel module: \"$l_mod_name\" is not loaded")
      else
         a_output2+=("  - kernel module: \"$l_mod_name\" is loaded")
      fi

      if grep -Pq -- '\binstall\h+'"${l_mod_chk_name//-/_}"'\h+(\/usr)?\/bin\/(true|false)\b' <<< "${a_showconfig[*]}"; then
         a_output+=("  - kernel module: \"$l_mod_name\" is not loadable")
      else
         a_output2+=("  - kernel module: \"$l_mod_name\" is loadable")
      fi

      if grep -Pq -- '\bblacklist\h+'"${l_mod_chk_name//-/_}"'\b' <<< "${a_showconfig[*]}"; then
         a_output+=("  - kernel module: \"$l_mod_name\" is deny listed")
      else
         a_output2+=("  - kernel module: \"$l_mod_name\" is not deny listed")
      fi
   }

   for l_mod_base_directory in "${l_mod_path[@]}"; do
      if [ -d "$l_mod_base_directory/${l_mod_name/-//}" ] && [ -n "$(ls -A "$l_mod_base_directory/${l_mod_name/-//}" 2>/dev/null)" ]; then
         a_output3+=("  - \"$l_mod_base_directory\"")
         l_mod_chk_name="$l_mod_name"
         [[ "$l_mod_name" =~ overlay ]] && l_mod_chk_name="${l_mod_name::-2}"
         [ "$l_dl" != "y" ] && f_module_chk
      else
         a_output+=(" - kernel module: \"$l_mod_name\" doesn't exist in \"$l_mod_base_directory\"")
      fi
   done

   [ "${#a_output3[@]}" -gt 0 ] && printf '%s\n' "" " -- INFO --" " - module: \"$l_mod_name\" exists in:" "${a_output3[@]}"
   if [ "${#a_output2[@]}" -le 0 ]; then
      printf '%s\n' "" "- Audit Result:" "  ** PASS **" "${a_output[@]}"
   else
      printf '%s\n' "" "- Audit Result:" "  ** FAIL **" " - Reason(s) for audit failure:" "${a_output2[@]}"
      [ "${#a_output[@]}" -gt 0 ] && printf '%s\n' "- Correctly set:" "${a_output[@]}"
   fi
}

BASH
    ],

    // --- 3.2.2 Ensure tipc kernel module is not available ---
    [
        'id' => '3.2.2', 'title' => 'Ensure tipc kernel module is not available', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
   a_output=()
   a_output2=()
   a_output3=()
   l_dl=""
   l_mod_name="tipc"
   l_mod_type="net"
   l_mod_path=($(find /lib/modules/ -type d -path "*/kernel/$l_mod_type" 2>/dev/null | sort -u))

   f_module_chk()
   {
      l_dl="y"
      a_showconfig=()
      while IFS= read -r l_showconfig; do
         a_showconfig+=("$l_showconfig")
      done < <(modprobe --showconfig | grep -P -- '\b(install|blacklist)\h+'"${l_mod_chk_name//-/_}"'\b')

      if ! lsmod | grep -q "$l_mod_chk_name"; then
         a_output+=("  - kernel module: \"$l_mod_name\" is not loaded")
      else
         a_output2+=("  - kernel module: \"$l_mod_name\" is loaded")
      fi

      if grep -Pq -- '\binstall\h+'"${l_mod_chk_name//-/_}"'\h+(\/usr)?\/bin\/(true|false)\b' <<< "${a_showconfig[*]}"; then
         a_output+=("  - kernel module: \"$l_mod_name\" is not loadable")
      else
         a_output2+=("  - kernel module: \"$l_mod_name\" is loadable")
      fi

      if grep -Pq -- '\bblacklist\h+'"${l_mod_chk_name//-/_}"'\b' <<< "${a_showconfig[*]}"; then
         a_output+=("  - kernel module: \"$l_mod_name\" is deny listed")
      else
         a_output2+=("  - kernel module: \"$l_mod_name\" is not deny listed")
      fi
   }

   for l_mod_base_directory in "${l_mod_path[@]}"; do
      if [ -d "$l_mod_base_directory/${l_mod_name/-//}" ] && [ -n "$(ls -A "$l_mod_base_directory/${l_mod_name/-//}" 2>/dev/null)" ]; then
         a_output3+=("  - \"$l_mod_base_directory\"")
         l_mod_chk_name="$l_mod_name"
         [[ "$l_mod_name" =~ overlay ]] && l_mod_chk_name="${l_mod_name::-2}"
         [ "$l_dl" != "y" ] && f_module_chk
      else
         a_output+=(" - kernel module: \"$l_mod_name\" doesn't exist in \"$l_mod_base_directory\"")
      fi
   done

   [ "${#a_output3[@]}" -gt 0 ] && printf '%s\n' "" " -- INFO --" " - module: \"$l_mod_name\" exists in:" "${a_output3[@]}"
   if [ "${#a_output2[@]}" -le 0 ]; then
      printf '%s\n' "" "- Audit Result:" "  ** PASS **" "${a_output[@]}"
   else
      printf '%s\n' "" "- Audit Result:" "  ** FAIL **" " - Reason(s) for audit failure:" "${a_output2[@]}"
      [ "${#a_output[@]}" -gt 0 ] && printf '%s\n' "- Correctly set:" "${a_output[@]}"
   fi
}

BASH
    ],

    // --- 3.2.3 Ensure rds kernel module is not available ---
    [
        'id' => '3.2.3', 'title' => 'Ensure rds kernel module is not available', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
   a_output=()
   a_output2=()
   a_output3=()
   l_dl=""
   l_mod_name="rds"
   l_mod_type="net"
   l_mod_path=($(find /lib/modules/ -type d -path "*/kernel/$l_mod_type" 2>/dev/null | sort -u))

   f_module_chk()
   {
      l_dl="y"
      a_showconfig=()
      while IFS= read -r l_showconfig; do
         a_showconfig+=("$l_showconfig")
      done < <(modprobe --showconfig | grep -P -- '\b(install|blacklist)\h+'"${l_mod_chk_name//-/_}"'\b')

      if ! lsmod | grep -q "$l_mod_chk_name"; then
         a_output+=("  - kernel module: \"$l_mod_name\" is not loaded")
      else
         a_output2+=("  - kernel module: \"$l_mod_name\" is loaded")
      fi

      if grep -Pq -- '\binstall\h+'"${l_mod_chk_name//-/_}"'\h+(\/usr)?\/bin\/(true|false)\b' <<< "${a_showconfig[*]}"; then
         a_output+=("  - kernel module: \"$l_mod_name\" is not loadable")
      else
         a_output2+=("  - kernel module: \"$l_mod_name\" is loadable")
      fi

      if grep -Pq -- '\bblacklist\h+'"${l_mod_chk_name//-/_}"'\b' <<< "${a_showconfig[*]}"; then
         a_output+=("  - kernel module: \"$l_mod_name\" is deny listed")
      else
         a_output2+=("  - kernel module: \"$l_mod_name\" is not deny listed")
      fi
   }

   for l_mod_base_directory in "${l_mod_path[@]}"; do
      if [ -d "$l_mod_base_directory/${l_mod_name/-//}" ] && [ -n "$(ls -A "$l_mod_base_directory/${l_mod_name/-//}" 2>/dev/null)" ]; then
         a_output3+=("  - \"$l_mod_base_directory\"")
         l_mod_chk_name="$l_mod_name"
         [[ "$l_mod_name" =~ overlay ]] && l_mod_chk_name="${l_mod_name::-2}"
         [ "$l_dl" != "y" ] && f_module_chk
      else
         a_output+=(" - kernel module: \"$l_mod_name\" doesn't exist in \"$l_mod_base_directory\"")
      fi
   done

   [ "${#a_output3[@]}" -gt 0 ] && printf '%s\n' "" " -- INFO --" " - module: \"$l_mod_name\" exists in:" "${a_output3[@]}"
   if [ "${#a_output2[@]}" -le 0 ]; then
      printf '%s\n' "" "- Audit Result:" "  ** PASS **" "${a_output[@]}"
   else
      printf '%s\n' "" "- Audit Result:" "  ** FAIL **" " - Reason(s) for audit failure:" "${a_output2[@]}"
      [ "${#a_output[@]}" -gt 0 ] && printf '%s\n' "- Correctly set:" "${a_output[@]}"
   fi
}

BASH
    ],

    // --- 3.2.4 Ensure sctp kernel module is not available ---
    [
        'id' => '3.2.4', 'title' => 'Ensure sctp kernel module is not available', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
   a_output=()
   a_output2=()
   a_output3=()
   l_dl=""
   l_mod_name="sctp"
   l_mod_type="net"
   l_mod_path=($(find /lib/modules/ -type d -path "*/kernel/$l_mod_type" 2>/dev/null | sort -u))

   f_module_chk()
   {
      l_dl="y"
      a_showconfig=()
      while IFS= read -r l_showconfig; do
         a_showconfig+=("$l_showconfig")
      done < <(modprobe --showconfig | grep -P -- '\b(install|blacklist)\h+'"${l_mod_chk_name//-/_}"'\b')

      if ! lsmod | grep -q "$l_mod_chk_name"; then
         a_output+=("  - kernel module: \"$l_mod_name\" is not loaded")
      else
         a_output2+=("  - kernel module: \"$l_mod_name\" is loaded")
      fi

      if grep -Pq -- '\binstall\h+'"${l_mod_chk_name//-/_}"'\h+(\/usr)?\/bin\/(true|false)\b' <<< "${a_showconfig[*]}"; then
         a_output+=("  - kernel module: \"$l_mod_name\" is not loadable")
      else
         a_output2+=("  - kernel module: \"$l_mod_name\" is loadable")
      fi

      if grep -Pq -- '\bblacklist\h+'"${l_mod_chk_name//-/_}"'\b' <<< "${a_showconfig[*]}"; then
         a_output+=("  - kernel module: \"$l_mod_name\" is deny listed")
      else
         a_output2+=("  - kernel module: \"$l_mod_name\" is not deny listed")
      fi
   }

   for l_mod_base_directory in "${l_mod_path[@]}"; do
      if [ -d "$l_mod_base_directory/${l_mod_name/-//}" ] && [ -n "$(ls -A "$l_mod_base_directory/${l_mod_name/-//}" 2>/dev/null)" ]; then
         a_output3+=("  - \"$l_mod_base_directory\"")
         l_mod_chk_name="$l_mod_name"
         [[ "$l_mod_name" =~ overlay ]] && l_mod_chk_name="${l_mod_name::-2}"
         [ "$l_dl" != "y" ] && f_module_chk
      else
         a_output+=(" - kernel module: \"$l_mod_name\" doesn't exist in \"$l_mod_base_directory\"")
      fi
   done

   [ "${#a_output3[@]}" -gt 0 ] && printf '%s\n' "" " -- INFO --" " - module: \"$l_mod_name\" exists in:" "${a_output3[@]}"

   if [ "${#a_output2[@]}" -le 0 ]; then
      printf '%s\n' "" "- Audit Result:" "  ** PASS **" "${a_output[@]}"
   else
      printf '%s\n' "" "- Audit Result:" "  ** FAIL **" " - Reason(s) for audit failure:" "${a_output2[@]}"
      [ "${#a_output[@]}" -gt 0 ] && printf '%s\n' "- Correctly set:" "${a_output[@]}"
   fi
}

BASH
    ],

    // --- 3.3 Configure Network Kernel Parameters ---
    [ 'id' => '3.3', 'title' => 'Configure Network Kernel Parameters', 'type' => 'header' ],

    // --- 3.3.1 Ensure ip forwarding is disabled ---
    [
        'id' => '3.3.1', 'title' => 'Ensure ip forwarding is disabled', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output=()
    a_output2=()
    # Define the full path to the sysctl command
    SYSCTL_CMD="/sbin/sysctl"

    # Check IPv4 forwarding
    ipv4_fw_run_val=$($SYSCTL_CMD net.ipv4.ip_forward | awk '{print $3}')
    if [ "$ipv4_fw_run_val" -eq 0 ]; then
        a_output+=("  - net.ipv4.ip_forward is correctly set to 0 in the running configuration.")
    else
        a_output2+=("  - net.ipv4.ip_forward is NOT set to 0 in the running configuration (current: $ipv4_fw_run_val).")
    fi

    ipv4_fw_config_val=$(grep -Ps -- '^\h*net\.ipv4\.ip_forward\h*=\h*1' /etc/sysctl.conf /etc/sysctl.d/*.conf)
    if [ -z "$ipv4_fw_config_val" ]; then
        a_output+=("  - net.ipv4.ip_forward is correctly set to 0 in configuration files.")
    else
        a_output2+=("  - net.ipv4.ip_forward is incorrectly enabled in a configuration file.")
    fi

    # Check IPv6 forwarding
    ipv6_fw_run_val=$($SYSCTL_CMD net.ipv6.conf.all.forwarding | awk '{print $3}')
    if [ "$ipv6_fw_run_val" -eq 0 ]; then
        a_output+=("  - net.ipv6.conf.all.forwarding is correctly set to 0 in the running configuration.")
    else
        a_output2+=("  - net.ipv6.conf.all.forwarding is NOT set to 0 in the running configuration (current: $ipv6_fw_run_val).")
    fi

    ipv6_fw_config_val=$(grep -Ps -- '^\h*net\.ipv6\.conf\.all\.forwarding\h*=\h*1' /etc/sysctl.conf /etc/sysctl.d/*.conf)
    if [ -z "$ipv6_fw_config_val" ]; then
        a_output+=("  - net.ipv6.conf.all.forwarding is correctly set to 0 in configuration files.")
    else
        a_output2+=("  - net.ipv6.conf.all.forwarding is incorrectly enabled in a configuration file.")
    fi

    # --- Display Results ---
    if [ "${#a_output2[@]}" -le 0 ]; then
        printf '%s\n' "" "- Audit Result:" "  ** PASS **"
        printf '%s\n' "${a_output[@]}"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output2[@]}"
        if [ "${#a_output[@]}" -gt 0 ]; then
            printf '\n%s\n' "- Correctly set:"
            printf '%s\n' "${a_output[@]}"
        fi
    fi
}
BASH
    ],

    // --- 3.3.2 Ensure packet redirect sending is disabled ---
    [
        'id' => '3.3.2', 'title' => 'Ensure packet redirect sending is disabled', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output=()
    a_output2=()
    l_ipv6_disabled=""
    a_parlist=("net.ipv4.conf.all.send_redirects=0" "net.ipv4.conf.default.send_redirects=0")
    SYSCTL_CMD="/sbin/sysctl"

    f_ipv6_chk() {
        l_ipv6_disabled="no"
        if ! grep -Pqs -- '^\h*0\b' /sys/module/ipv6/parameters/disable && \
           ! ( "$SYSCTL_CMD" net.ipv6.conf.all.disable_ipv6 | grep -Pqs -- "=\h*1\b" && \
               "$SYSCTL_CMD" net.ipv6.conf.default.disable_ipv6 | grep -Pqs -- "=\h*1\b" ); then
            l_ipv6_disabled="no"
        else
            l_ipv6_disabled="yes"
        fi
    }

    f_kernel_parameter_chk() {
        l_running_parameter_value=$("$SYSCTL_CMD" -n "$l_parameter_name" 2>/dev/null | xargs)
        if grep -Pq -- "^\s*$l_parameter_value\s*$" <<< "$l_running_parameter_value"; then
            a_output+=("  - \"$l_parameter_name\" is correctly set to \"$l_running_parameter_value\" in the running configuration")
        else
            a_output2+=("  - \"$l_parameter_name\" is incorrectly set to \"$l_running_parameter_value\" in the running configuration and should be \"$l_value_out\"")
        fi

        config_file_check=$(grep -Psrh -- "^\h*$l_parameter_name\h*=" /etc/sysctl.conf /etc/sysctl.d/*.conf)
        if [ -n "$config_file_check" ]; then
            last_config_setting=$(echo "$config_file_check" | tail -n 1)
            config_val=$(echo "$last_config_setting" | awk -F= '{print $2}' | xargs)
            if grep -Pq -- "^\s*$l_parameter_value\s*$" <<< "$config_val"; then
                a_output+=("  - \"$l_parameter_name\" is correctly set to \"$config_val\" in configuration files.")
            else
                a_output2+=("  - \"$l_parameter_name\" is incorrectly set to \"$config_val\" in configuration files and should be \"$l_value_out\"")
            fi
        else
            a_output2+=("  - \"$l_parameter_name\" is not set in a configuration file.")
        fi
    }

    while IFS="=" read -r l_parameter_name l_parameter_value; do
        l_parameter_name=$(xargs <<< "$l_parameter_name")
        l_parameter_value=$(xargs <<< "$l_parameter_value")
        l_value_out="$l_parameter_value"

        if [[ "$l_parameter_name" == net.ipv6.* ]]; then
            [ -z "$l_ipv6_disabled" ] && f_ipv6_chk
            if [ "$l_ipv6_disabled" == "yes" ]; then
                a_output+=("  - IPv6 is disabled, \"$l_parameter_name\" check is not applicable.")
            else
                f_kernel_parameter_chk
            fi
        else
            f_kernel_parameter_chk
        fi
    done < <(printf '%s\n' "${a_parlist[@]}")

    if [ "${#a_output2[@]}" -le 0 ]; then
        printf '%s\n' "" "- Audit Result:" "  ** PASS **"
        printf '%s\n' "${a_output[@]}"
    else
        printf '%s\n' "" "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output2[@]}"
        if [ "${#a_output[@]}" -gt 0 ]; then
            printf '\n%s\n' "- Correctly set:"
            printf '%s\n' "${a_output[@]}"
        fi
    fi
}
BASH
    ],

    // --- 3.3.3 Ensure bogus icmp responses are ignored ---
    [
        'id' => '3.3.3', 'title' => 'Ensure bogus icmp responses are ignored', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output=()
    a_output2=()
    SYSCTL_CMD="/sbin/sysctl" # Use full path for reliability

    # --- Check Running Configuration ---
    running_val=$($SYSCTL_CMD -n net.ipv4.icmp_ignore_bogus_error_responses 2>/dev/null | xargs)
    if [ "$running_val" -eq 1 ]; then
        a_output+=("  - Running configuration for 'net.ipv4.icmp_ignore_bogus_error_responses' is correctly set to '1'.")
    else
        a_output2+=("  - Running configuration for 'net.ipv4.icmp_ignore_bogus_error_responses' is incorrectly set to '$running_val'.")
    fi

    # --- Check Configuration Files ---
    # Use grep to find the last active setting for the parameter in config files
    config_check=$(grep -Psrh -- "^\h*net\.ipv4\.icmp_ignore_bogus_error_responses\h*=" /etc/sysctl.conf /etc/sysctl.d/*.conf /usr/lib/sysctl.d/*.conf /run/sysctl.d/*.conf | tail -n 1)

    if [ -n "$config_check" ]; then
        config_val=$(echo "$config_check" | awk -F= '{print $2}' | xargs)
        if [ "$config_val" -eq 1 ]; then
            a_output+=("  - Configuration file setting for 'net.ipv4.icmp_ignore_bogus_error_responses' is correctly set to '1'.")
        else
            a_output2+=("  - Configuration file setting for 'net.ipv4.icmp_ignore_bogus_error_responses' is incorrectly set to '$config_val'.")
        fi
    else
        a_output2+=("  - 'net.ipv4.icmp_ignore_bogus_error_responses' is not set in any configuration file.")
    fi

    # --- Display Results ---
    if [ "${#a_output2[@]}" -le 0 ]; then
        printf '%s\n' "" "- Audit Result:" "  ** PASS **"
        printf '%s\n' "${a_output[@]}"
    else
        printf '%s\n' "" "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output2[@]}"
        if [ "${#a_output[@]}" -gt 0 ]; then
            printf '\n%s\n' "- Correctly set:"
            printf '%s\n' "${a_output[@]}"
        fi
    fi
}
BASH
    ],

    // --- 3.3.4 Ensure broadcast icmp requests are ignored ---
    [
        'id' => '3.3.4', 'title' => 'Ensure broadcast icmp requests are ignored', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output=()
    a_output2=()
    SYSCTL_CMD="/sbin/sysctl" # Use full path for reliability
    PARAM_NAME="net.ipv4.icmp_echo_ignore_broadcasts"
    EXPECTED_VAL=1

    # --- Check Running Configuration ---
    running_val=$($SYSCTL_CMD -n "$PARAM_NAME" 2>/dev/null | xargs)
    if [ "$running_val" -eq "$EXPECTED_VAL" ]; then
        a_output+=("  - Running configuration for '$PARAM_NAME' is correctly set to '$EXPECTED_VAL'.")
    else
        a_output2+=("  - Running configuration for '$PARAM_NAME' is incorrectly set to '$running_val'.")
    fi

    # --- Check Configuration Files ---
    config_check=$(grep -Psrh -- "^\h*$PARAM_NAME\h*=" /etc/sysctl.conf /etc/sysctl.d/*.conf /usr/lib/sysctl.d/*.conf /run/sysctl.d/*.conf | tail -n 1)

    if [ -n "$config_check" ]; then
        config_val=$(echo "$config_check" | awk -F= '{print $2}' | xargs)
        if [ "$config_val" -eq "$EXPECTED_VAL" ]; then
            a_output+=("  - Configuration file setting for '$PARAM_NAME' is correctly set to '$EXPECTED_VAL'.")
        else
            a_output2+=("  - Configuration file setting for '$PARAM_NAME' is incorrectly set to '$config_val'.")
        fi
    else
        a_output2+=("  - '$PARAM_NAME' is not set in any configuration file.")
    fi

    # --- Display Results ---
    if [ "${#a_output2[@]}" -le 0 ]; then
        printf '%s\n' "" "- Audit Result:" "  ** PASS **"
        printf '%s\n' "${a_output[@]}"
    else
        printf '%s\n' "" "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output2[@]}"
        if [ "${#a_output[@]}" -gt 0 ]; then
            printf '\n%s\n' "- Correctly set:"
            printf '%s\n' "${a_output[@]}"
        fi
    fi
}
BASH
    ],

    // --- 3.3.5 Ensure icmp redirects are not accepted  ---
    [
        'id' => '3.3.5', 'title' => 'Ensure icmp redirects are not accepted ', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output=()
    a_output2=()
    l_ipv6_disabled=""
    SYSCTL_CMD="/sbin/sysctl"

    a_parlist=(
        "net.ipv4.conf.all.accept_redirects=0"
        "net.ipv4.conf.default.accept_redirects=0"
        "net.ipv6.conf.all.accept_redirects=0"
        "net.ipv6.conf.default.accept_redirects=0"
    )

    f_ipv6_chk() {
        if "$SYSCTL_CMD" -n net.ipv6.conf.all.disable_ipv6 2>/dev/null | grep -q "1"; then
            l_ipv6_disabled="yes"
        else
            l_ipv6_disabled="no"
        fi
    }

    for par_entry in "${a_parlist[@]}"; do
        l_parameter_name=$(cut -d= -f1 <<< "$par_entry")
        l_expected_value=$(cut -d= -f2 <<< "$par_entry")

        if [[ "$l_parameter_name" == net.ipv6.* ]]; then
            [ -z "$l_ipv6_disabled" ] && f_ipv6_chk
            if [ "$l_ipv6_disabled" == "yes" ]; then
                a_output+=("  - IPv6 is disabled, '$l_parameter_name' check is not applicable.")
                continue
            fi
        fi

        running_val=$($SYSCTL_CMD -n "$l_parameter_name" 2>/dev/null)
        if [ "$running_val" == "$l_expected_value" ]; then
            a_output+=("  - Running config for '$l_parameter_name' is correctly set to '$running_val'.")
        else
            a_output2+=("  - Running config for '$l_parameter_name' is incorrectly set to '$running_val'.")
        fi

        config_check=$(grep -Psrh -- "^\h*$l_parameter_name\h*=" /etc/sysctl.conf /etc/sysctl.d/*.conf /usr/lib/sysctl.d/*.conf /run/sysctl.d/*.conf | tail -n 1)
        if [ -n "$config_check" ]; then
            config_val=$(echo "$config_check" | awk -F= '{print $2}' | xargs)
            if [ "$config_val" == "$l_expected_value" ]; then
                a_output+=("  - On-disk config for '$l_parameter_name' is correctly set to '$config_val'.")
            else
                a_output2+=("  - On-disk config for '$l_parameter_name' is incorrectly set to '$config_val'.")
            fi
        else
            a_output2+=("  - '$l_parameter_name' is not set in any configuration file.")
        fi
    done

    if [ "${#a_output2[@]}" -le 0 ]; then
        printf '%s\n' "" "- Audit Result:" "  ** PASS **"
        printf '%s\n' "${a_output[@]}"
    else
        printf '%s\n' "" "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output2[@]}"
        if [ "${#a_output[@]}" -gt 0 ]; then
            printf '\n%s\n' "- Correctly set:"
            printf '%s\n' "${a_output[@]}"
        fi
    fi
}
BASH
    ],

    // --- 3.3.6 Ensure secure icmp redirects are not accepted  ---
    [
        'id' => '3.3.6', 'title' => 'Ensure secure icmp redirects are not accepted ', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output=()
    a_output2=()
    SYSCTL_CMD="/sbin/sysctl"

    a_parlist=(
        "net.ipv4.conf.all.secure_redirects=0"
        "net.ipv4.conf.default.secure_redirects=0"
    )

    for par_entry in "${a_parlist[@]}"; do
        l_parameter_name=$(cut -d= -f1 <<< "$par_entry")
        l_expected_value=$(cut -d= -f2 <<< "$par_entry")

        running_val=$($SYSCTL_CMD -n "$l_parameter_name" 2>/dev/null)
        if [ "$running_val" == "$l_expected_value" ]; then
            a_output+=("  - Running config for '$l_parameter_name' is correctly set to '$running_val'.")
        else
            a_output2+=("  - Running config for '$l_parameter_name' is incorrectly set to '$running_val'.")
        fi

        config_check=$(grep -Psrh -- "^\h*$l_parameter_name\h*=" /etc/sysctl.conf /etc/sysctl.d/*.conf /usr/lib/sysctl.d/*.conf /run/sysctl.d/*.conf | tail -n 1)
        if [ -n "$config_check" ]; then
            config_val=$(echo "$config_check" | awk -F= '{print $2}' | xargs)
            if [ "$config_val" == "$l_expected_value" ]; then
                a_output+=("  - On-disk config for '$l_parameter_name' is correctly set to '$config_val'.")
            else
                a_output2+=("  - On-disk config for '$l_parameter_name' is incorrectly set to '$config_val'.")
            fi
        else
            a_output2+=("  - '$l_parameter_name' is not set in any configuration file.")
        fi
    done

    if [ "${#a_output2[@]}" -le 0 ]; then
        printf '%s\n' "" "- Audit Result:" "  ** PASS **"
        printf '%s\n' "${a_output[@]}"
    else
        printf '%s\n' "" "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output2[@]}"
        if [ "${#a_output[@]}" -gt 0 ]; then
            printf '\n%s\n' "- Correctly set:"
            printf '%s\n' "${a_output[@]}"
        fi
    fi
}
BASH
    ],

    // --- 3.3.7 Ensure reverse path filtering is enabled ---
    [
        'id' => '3.3.7', 'title' => 'Ensure reverse path filtering is enabled', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
   a_output=(); a_output2=(); l_ipv6_disabled=""
   a_parlist=("net.ipv4.conf.all.rp_filter=1" "net.ipv4.conf.default.rp_filter=1")
   l_ufwscf="$([ -f /etc/default/ufw ] && awk -F= '/^\s*IPT_SYSCTL=/ {print $2}' /etc/default/ufw)"
   f_ipv6_chk()
   {
      l_ipv6_disabled="no"
      ! grep -Pqs -- '^\h*0\b' /sys/module/ipv6/parameters/disable && l_ipv6_disabled="yes"
      if sysctl net.ipv6.conf.all.disable_ipv6 2>/dev/null | grep -Pqs -- "^\h*net\.ipv6\.conf\.all\.disable_ipv6\h*=\h*1\b" && \
         sysctl net.ipv6.conf.default.disable_ipv6 2>/dev/null | grep -Pqs -- "^\h*net\.ipv6\.conf\.default\.disable_ipv6\h*=\h*1\b"; then
         l_ipv6_disabled="yes"
      fi
   }

   f_kernel_parameter_chk()
   {
      l_running_parameter_value="$(sysctl "$l_parameter_name" 2>/dev/null | awk -F= '{print $2}' | xargs)"
      if grep -Pq -- '\b'"$l_parameter_value"'\b' <<< "$l_running_parameter_value"; then
         a_output+=(" - \"$l_parameter_name\" is correctly set to \"$l_running_parameter_value\"" \
         "    in the running configuration")
      else
         a_output2+=(" - \"$l_parameter_name\" is incorrectly set to \"$l_running_parameter_value\"" \
         "    in the running configuration" \
         "    and should have a value of: \"$l_value_out\"")
      fi

      unset A_out; declare -A A_out
      while read -r l_out; do
         if [ -n "$l_out" ]; then
            if [[ $l_out =~ ^\s*# ]]; then
               l_file="${l_out//# /}"
            else
               l_kpar="$(awk -F= '{print $1}' <<< "$l_out" | xargs)"
               [ "$l_kpar" = "$l_parameter_name" ] && A_out+=(["$l_kpar"]="$l_file")
            fi
         fi
      done < <(/lib/systemd/systemd-sysctl --cat-config | grep -Po '^\h*([^#\n\r]+|#\h*/[^#\n\r\h]+\.conf\b)')

      if [ -n "$l_ufwscf" ]; then
         l_kpar="$(grep -Po "^\h*$l_parameter_name\b" "$l_ufwscf" | xargs)"
         l_kpar="${l_kpar//\//.}"
         [ "$l_kpar" = "$l_parameter_name" ] && A_out+=(["$l_kpar"]="$l_ufwscf")
      fi

      if (( ${#A_out[@]} > 0 )); then
         while IFS="=" read -r l_fkpname l_file_parameter_value; do
            l_fkpname="${l_fkpname// /}"
            l_file_parameter_value="${l_file_parameter_value// /}"
            if grep -Pq -- '\b'"$l_parameter_value"'\b' <<< "$l_file_parameter_value"; then
               a_output+=(" - \"$l_parameter_name\" is correctly set to \"$l_file_parameter_value\"" \
               "    in \"$(printf '%s' "${A_out[@]}")\"")
            else
               a_output2+=(" - \"$l_parameter_name\" is incorrectly set to \"$l_file_parameter_value\"" \
               "    in \"$(printf '%s' "${A_out[@]}")\"" \
               "    and should have a value of: \"$l_value_out\"")
            fi
         done < <(grep -Po -- "^\h*$l_parameter_name\h*=\h*\H+" "${A_out[@]}")
      else
         a_output2+=(" - \"$l_parameter_name\" is not set in an included file" \
         "    ** Note: \"$l_parameter_name\" May be set in a file that's ignored by load procedure **")
      fi
   }

   while IFS="=" read -r l_parameter_name l_parameter_value; do
      l_parameter_name="${l_parameter_name// /}"
      l_parameter_value="${l_parameter_value// /}"
      l_value_out="${l_parameter_value//-/ through }"
      l_value_out="${l_value_out//|/ or }"
      l_value_out="$(tr -d '(){}' <<< "$l_value_out")"
      if grep -q '^net.ipv6.' <<< "$l_parameter_name"; then
         [ -z "$l_ipv6_disabled" ] && f_ipv6_chk
         if [ "$l_ipv6_disabled" = "yes" ]; then
            a_output+=(" - IPv6 is disabled on the system, \"$l_parameter_name\" is not applicable")
         else
            f_kernel_parameter_chk
         fi
      else
         f_kernel_parameter_chk
      fi
   done < <(printf '%s\n' "${a_parlist[@]}")

   if [ "${#a_output2[@]}" -le 0 ]; then
      printf '%s\n' "" "- Audit Result:" "  ** PASS **" "${a_output[@]}" ""
   else
      printf '%s\n' "" "- Audit Result:" "  ** FAIL **" " - Reason(s) for audit failure:" "${a_output2[@]}"
      [ "${#a_output[@]}" -gt 0 ] && printf '%s\n' "" "- Correctly set:" "${a_output[@]}" ""
   fi
}
BASH
    ],

    // --- 3.3.8 Ensure source routed packets are not accepted ---
    [
        'id' => '3.3.8', 'title' => 'Ensure source routed packets are not accepted', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output=()
    a_output2=()
    l_ipv6_disabled=""
    a_parlist=("net.ipv4.conf.all.rp_filter=1" "net.ipv4.conf.default.rp_filter=1")
    l_ufwscf="$([ -f /etc/default/ufw ] && awk -F= '/^\s*IPT_SYSCTL=/ {print $2}' /etc/default/ufw)"

    f_ipv6_chk() {
        l_ipv6_disabled="no"
        if ! grep -Pqs -- '^\h*0\b' /sys/module/ipv6/parameters/disable && \
           ! ( /sbin/sysctl net.ipv6.conf.all.disable_ipv6 | grep -Pqs -- "^\h*net\.ipv6\.conf\.all\.disable_ipv6\h*=\h*1\b" && \
               /sbin/sysctl net.ipv6.conf.default.disable_ipv6 | grep -Pqs -- "^\h*net\.ipv6\.conf\.default\.disable_ipv6\h*=\h*1\b" ); then
            l_ipv6_disabled="no"
        else
            l_ipv6_disabled="yes"
        fi
    }

    f_kernel_parameter_chk() {
        l_running_parameter_value="$(/sbin/sysctl "$l_parameter_name" | awk -F= '{print $2}' | xargs)"
        if grep -Pq -- '\b'"$l_parameter_value"'\b' <<< "$l_running_parameter_value"; then
            a_output+=(" - \"$l_parameter_name\" is correctly set to \"$l_running_parameter_value\" in the running configuration")
        else
            a_output2+=(" - \"$l_parameter_name\" is incorrectly set to \"$l_running_parameter_value\" in the running configuration and should have a value of: \"$l_value_out\"")
        fi

        unset A_out
        declare -A A_out

        while read -r l_out; do
            if [ -n "$l_out" ]; then
                if [[ "$l_out" =~ ^\s*# ]]; then
                    l_file="${l_out//# /}"
                else
                    l_kpar="$(awk -F= '{print $1}' <<< "$l_out" | xargs)"
                    if [ "$l_kpar" = "$l_parameter_name" ]; then
                        A_out["$l_kpar"]="$l_file"
                    fi
                fi
            fi
        done < <("$l_systemdsysctl" --cat-config | grep -Po '^\h*([^#\n\r]+|#\h*\/[^#\n\r\h]+\.conf\b)')

        if [ -n "$l_ufwscf" ]; then
            l_kpar="$(grep -Po "^\h*$l_parameter_name\b" "$l_ufwscf" | xargs)"
            l_kpar="${l_kpar//\//.}"
            if [ "$l_kpar" = "$l_parameter_name" ]; then
                A_out["$l_kpar"]="$l_ufwscf"
            fi
        fi

        if (( ${#A_out[@]} > 0 )); then
            while IFS="=" read -r l_fkpname l_file_parameter_value; do
                l_fkpname="${l_fkpname// /}"
                l_file_parameter_value="${l_file_parameter_value// /}"
                if grep -Pq -- '\b'"$l_parameter_value"'\b' <<< "$l_file_parameter_value"; then
                    a_output+=(" - \"$l_parameter_name\" is correctly set to \"$l_file_parameter_value\" in \"$(printf '%s' "${A_out[@]}")\"")
                else
                    a_output2+=(" - \"$l_parameter_name\" is incorrectly set to \"$l_file_parameter_value\" in \"$(printf '%s' "${A_out[@]}")\" and should have a value of: \"$l_value_out\"")
                fi
            done < <(grep -Po -- "^\h*$l_parameter_name\h*=\h*\H+" "${A_out[@]}")
        else
            a_output2+=(" - \"$l_parameter_name\" is not set in an included file ** Note: \"$l_parameter_name\" May be set in a file that's ignored by load procedure **")
        fi
    }

    l_systemdsysctl="$(readlink -f /lib/systemd/systemd-sysctl)"

    while IFS="=" read -r l_parameter_name l_parameter_value; do
        l_parameter_name="${l_parameter_name// /}"
        l_parameter_value="${l_parameter_value// /}"
        l_value_out="${l_parameter_value//-/ through }"
        l_value_out="${l_value_out//|/ or }"
        l_value_out="$(tr -d '(){}' <<< "$l_value_out")"

        if grep -q '^net.ipv6.' <<< "$l_parameter_name"; then
            [ -z "$l_ipv6_disabled" ] && f_ipv6_chk
            if [ "$l_ipv6_disabled" = "yes" ]; then
                a_output+=(" - IPv6 is disabled on the system, \"$l_parameter_name\" is not applicable")
            else
                f_kernel_parameter_chk
            fi
        else
            f_kernel_parameter_chk
        fi
    done < <(printf '%s\n' "${a_parlist[@]}")

    if [ "${#a_output2[@]}" -le 0 ]; then
        printf '%s\n' "" "- Audit Result:" "  ** PASS **" "${a_output[@]}" ""
    else
        printf '%s\n' "" "- Audit Result:" "  ** FAIL **" " - Reason(s) for audit failure:" "${a_output2[@]}"
        if [ "${#a_output[@]}" -gt 0 ]; then
            printf '%s\n' "" "- Correctly set:" "${a_output[@]}" ""
        fi
    fi
}
BASH
    ],

    // --- 3.3.9 Ensure suspicious packets are logged ---
    [
        'id' => '3.3.9', 'title' => 'Ensure suspicious packets are logged', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output=()
    a_output2=()
    l_ipv6_disabled=""
    a_parlist=("net.ipv4.conf.all.log_martians=1" "net.ipv4.conf.default.log_martians=1")
    l_ufwscf="$([ -f /etc/default/ufw ] && awk -F= '/^\s*IPT_SYSCTL=/ {print $2}' /etc/default/ufw)"

    f_ipv6_chk() {
        l_ipv6_disabled="no"
        if ! grep -Pqs -- '^\h*0\b' /sys/module/ipv6/parameters/disable && \
           ! ( /sbin/sysctl net.ipv6.conf.all.disable_ipv6 | grep -Pqs -- "^\h*net\.ipv6\.conf\.all\.disable_ipv6\h*=\h*1\b" && \
               /sbin/sysctl net.ipv6.conf.default.disable_ipv6 | grep -Pqs -- "^\h*net\.ipv6\.conf\.default\.disable_ipv6\h*=\h*1\b" ); then
            l_ipv6_disabled="no"
        else
            l_ipv6_disabled="yes"
        fi
    }

    f_kernel_parameter_chk() {
        l_running_parameter_value="$(/sbin/sysctl "$l_parameter_name" | awk -F= '{print $2}' | xargs)"
        if grep -Pq -- '\b'"$l_parameter_value"'\b' <<< "$l_running_parameter_value"; then
            a_output+=(" - \"$l_parameter_name\" is correctly set to \"$l_running_parameter_value\" in the running configuration")
        else
            a_output2+=(" - \"$l_parameter_name\" is incorrectly set to \"$l_running_parameter_value\" in the running configuration and should have a value of: \"$l_value_out\"")
        fi

        unset A_out
        declare -A A_out

        while read -r l_out; do
            if [ -n "$l_out" ]; then
                if [[ "$l_out" =~ ^\s*# ]]; then
                    l_file="${l_out//# /}"
                else
                    l_kpar="$(awk -F= '{print $1}' <<< "$l_out" | xargs)"
                    if [ "$l_kpar" = "$l_parameter_name" ]; then
                        A_out["$l_kpar"]="$l_file"
                    fi
                fi
            fi
        done < <("$l_systemdsysctl" --cat-config | grep -Po '^\h*([^#\n\r]+|#\h*\/[^#\n\r\h]+\.conf\b)')

        if [ -n "$l_ufwscf" ]; then
            l_kpar="$(grep -Po "^\h*$l_parameter_name\b" "$l_ufwscf" | xargs)"
            l_kpar="${l_kpar//\//.}"
            if [ "$l_kpar" = "$l_parameter_name" ]; then
                A_out["$l_kpar"]="$l_ufwscf"
            fi
        fi

        if (( ${#A_out[@]} > 0 )); then
            while IFS="=" read -r l_fkpname l_file_parameter_value; do
                l_fkpname="${l_fkpname// /}"
                l_file_parameter_value="${l_file_parameter_value// /}"
                if grep -Pq -- '\b'"$l_parameter_value"'\b' <<< "$l_file_parameter_value"; then
                    a_output+=(" - \"$l_parameter_name\" is correctly set to \"$l_file_parameter_value\" in \"$(printf '%s' "${A_out[@]}")\"")
                else
                    a_output2+=(" - \"$l_parameter_name\" is incorrectly set to \"$l_file_parameter_value\" in \"$(printf '%s' "${A_out[@]}")\" and should have a value of: \"$l_value_out\"")
                fi
            done < <(grep -Po -- "^\h*$l_parameter_name\h*=\h*\H+" "${A_out[@]}")
        else
            a_output2+=(" - \"$l_parameter_name\" is not set in an included file ** Note: \"$l_parameter_name\" May be set in a file that's ignored by load procedure **")
        fi
    }

    l_systemdsysctl="$(readlink -f /lib/systemd/systemd-sysctl)"

    while IFS="=" read -r l_parameter_name l_parameter_value; do
        l_parameter_name="${l_parameter_name// /}"
        l_parameter_value="${l_parameter_value// /}"
        l_value_out="${l_parameter_value//-/ through }"
        l_value_out="${l_value_out//|/ or }"
        l_value_out="$(tr -d '(){}' <<< "$l_value_out")"

        if grep -q '^net.ipv6.' <<< "$l_parameter_name"; then
            [ -z "$l_ipv6_disabled" ] && f_ipv6_chk
            if [ "$l_ipv6_disabled" = "yes" ]; then
                a_output+=(" - IPv6 is disabled on the system, \"$l_parameter_name\" is not applicable")
            else
                f_kernel_parameter_chk
            fi
        else
            f_kernel_parameter_chk
        fi
    done < <(printf '%s\n' "${a_parlist[@]}")

    if [ "${#a_output2[@]}" -le 0 ]; then
        printf '%s\n' "" "- Audit Result:" "  ** PASS **" "${a_output[@]}" ""
    else
        printf '%s\n' "" "- Audit Result:" "  ** FAIL **" " - Reason(s) for audit failure:" "${a_output2[@]}"
        if [ "${#a_output[@]}" -gt 0 ]; then
            printf '%s\n' "" "- Correctly set:" "${a_output[@]}" ""
        fi
    fi
}
BASH
    ],

    // --- 3.3.10 Ensure tcp syn cookies is enabled ---
    [
        'id' => '3.3.10', 'title' => 'Ensure tcp syn cookies is enabled', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output=()
    a_output2=()
    l_ipv6_disabled=""
    a_parlist=("net.ipv4.tcp_syncookies=1")
    l_ufwscf="$([ -f /etc/default/ufw ] && awk -F= '/^\s*IPT_SYSCTL=/ {print $2}' /etc/default/ufw)"

    f_ipv6_chk() {
        l_ipv6_disabled="no"
        if ! grep -Pqs -- '^\h*0\b' /sys/module/ipv6/parameters/disable && \
           ! ( /sbin/sysctl net.ipv6.conf.all.disable_ipv6 | grep -Pqs -- "^\h*net\.ipv6\.conf\.all\.disable_ipv6\h*=\h*1\b" && \
               /sbin/sysctl net.ipv6.conf.default.disable_ipv6 | grep -Pqs -- "^\h*net\.ipv6\.conf\.default\.disable_ipv6\h*=\h*1\b" ); then
            l_ipv6_disabled="no"
        else
            l_ipv6_disabled="yes"
        fi
    }

    f_kernel_parameter_chk() {
        l_running_parameter_value="$(/sbin/sysctl "$l_parameter_name" | awk -F= '{print $2}' | xargs)"
        if grep -Pq -- '\b'"$l_parameter_value"'\b' <<< "$l_running_parameter_value"; then
            a_output+=(" - \"$l_parameter_name\" is correctly set to \"$l_running_parameter_value\" in the running configuration")
        else
            a_output2+=(" - \"$l_parameter_name\" is incorrectly set to \"$l_running_parameter_value\" in the running configuration and should have a value of: \"$l_value_out\"")
        fi

        unset A_out
        declare -A A_out

        while read -r l_out; do
            if [ -n "$l_out" ]; then
                if [[ "$l_out" =~ ^\s*# ]]; then
                    l_file="${l_out//# /}"
                else
                    l_kpar="$(awk -F= '{print $1}' <<< "$l_out" | xargs)"
                    if [ "$l_kpar" = "$l_parameter_name" ]; then
                        A_out["$l_kpar"]="$l_file"
                    fi
                fi
            fi
        done < <("$l_systemdsysctl" --cat-config | grep -Po '^\h*([^#\n\r]+|#\h*\/[^#\n\r\h]+\.conf\b)')

        if [ -n "$l_ufwscf" ]; then
            l_kpar="$(grep -Po "^\h*$l_parameter_name\b" "$l_ufwscf" | xargs)"
            l_kpar="${l_kpar//\//.}"
            if [ "$l_kpar" = "$l_parameter_name" ]; then
                A_out["$l_kpar"]="$l_ufwscf"
            fi
        fi

        if (( ${#A_out[@]} > 0 )); then
            while IFS="=" read -r l_fkpname l_file_parameter_value; do
                l_fkpname="${l_fkpname// /}"
                l_file_parameter_value="${l_file_parameter_value// /}"
                if grep -Pq -- '\b'"$l_parameter_value"'\b' <<< "$l_file_parameter_value"; then
                    a_output+=(" - \"$l_parameter_name\" is correctly set to \"$l_file_parameter_value\" in \"$(printf '%s' "${A_out[@]}")\"")
                else
                    a_output2+=(" - \"$l_parameter_name\" is incorrectly set to \"$l_file_parameter_value\" in \"$(printf '%s' "${A_out[@]}")\" and should have a value of: \"$l_value_out\"")
                fi
            done < <(grep -Po -- "^\h*$l_parameter_name\h*=\h*\H+" "${A_out[@]}")
        else
            a_output2+=(" - \"$l_parameter_name\" is not set in an included file ** Note: \"$l_parameter_name\" May be set in a file that's ignored by load procedure **")
        fi
    }

    l_systemdsysctl="$(readlink -f /lib/systemd/systemd-sysctl)"

    while IFS="=" read -r l_parameter_name l_parameter_value; do
        l_parameter_name="${l_parameter_name// /}"
        l_parameter_value="${l_parameter_value// /}"
        l_value_out="${l_parameter_value//-/ through }"
        l_value_out="${l_value_out//|/ or }"
        l_value_out="$(tr -d '(){}' <<< "$l_value_out")"

        if grep -q '^net.ipv6.' <<< "$l_parameter_name"; then
            [ -z "$l_ipv6_disabled" ] && f_ipv6_chk
            if [ "$l_ipv6_disabled" = "yes" ]; then
                a_output+=(" - IPv6 is disabled on the system, \"$l_parameter_name\" is not applicable")
            else
                f_kernel_parameter_chk
            fi
        else
            f_kernel_parameter_chk
        fi
    done < <(printf '%s\n' "${a_parlist[@]}")

    if [ "${#a_output2[@]}" -le 0 ]; then
        printf '%s\n' "" "- Audit Result:" "  ** PASS **" "${a_output[@]}" ""
    else
        printf '%s\n' "" "- Audit Result:" "  ** FAIL **" " - Reason(s) for audit failure:" "${a_output2[@]}"
        if [ "${#a_output[@]}" -gt 0 ]; then
            printf '%s\n' "" "- Correctly set:" "${a_output[@]}" ""
        fi
    fi
}
BASH
    ],

    // --- 3.3.11 Ensure ipv6 router advertisements are not accepted ---
    [
        'id' => '3.3.11', 'title' => 'Ensure ipv6 router advertisements are not accepted', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output=()
    a_output2=()
    l_ipv6_disabled=""
    a_parlist=("net.ipv4.conf.all.accept_source_route=0" "net.ipv4.conf.default.accept_source_route=0" "net.ipv6.conf.all.accept_source_route=0" "net.ipv6.conf.default.accept_source_route=0")
    SYSCTL_CMD="/sbin/sysctl"

    f_ipv6_chk() {
        if "$SYSCTL_CMD" -n net.ipv6.conf.all.disable_ipv6 2>/dev/null | grep -q "1"; then
            l_ipv6_disabled="yes"
        else
            l_ipv6_disabled="no"
        fi
    }

    f_kernel_parameter_chk() {
        running_val=$($SYSCTL_CMD -n "$l_parameter_name" 2>/dev/null)
        if [ "$running_val" == "$l_parameter_value" ]; then
            a_output+=("  - Running config for '$l_parameter_name' is correctly set to '$running_val'.")
        else
            a_output2+=("  - Running config for '$l_parameter_name' is incorrectly set to '$running_val'.")
        fi

        config_check=$(grep -Psrh -- "^\h*$l_parameter_name\h*=" /etc/sysctl.conf /etc/sysctl.d/*.conf /usr/lib/sysctl.d/*.conf /run/sysctl.d/*.conf | tail -n 1)
        if [ -n "$config_check" ]; then
            config_val=$(echo "$config_check" | awk -F= '{print $2}' | xargs)
            if [ "$config_val" == "$l_parameter_value" ]; then
                a_output+=("  - On-disk config for '$l_parameter_name' is correctly set to '$config_val'.")
            else
                a_output2+=("  - On-disk config for '$l_parameter_name' is incorrectly set to '$config_val'.")
            fi
        else
            a_output2+=("  - '$l_parameter_name' is not set in any configuration file.")
        fi
    }

    for par_entry in "${a_parlist[@]}"; do
        l_parameter_name=$(cut -d= -f1 <<< "$par_entry")
        l_parameter_value=$(cut -d= -f2 <<< "$par_entry")

        if [[ "$l_parameter_name" == net.ipv6.* ]]; then
            [ -z "$l_ipv6_disabled" ] && f_ipv6_chk
            if [ "$l_ipv6_disabled" == "yes" ]; then
                a_output+=("  - IPv6 is disabled, '$l_parameter_name' check is not applicable.")
                continue
            fi
        fi
        f_kernel_parameter_chk
    done

    if [ "${#a_output2[@]}" -le 0 ]; then
        printf '%s\n' "" "- Audit Result:" "  ** PASS **"
        printf '%s\n' "${a_output[@]}"
    else
        printf '%s\n' "" "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output2[@]}"
        if [ "${#a_output[@]}" -gt 0 ]; then
            printf '\n%s\n' "- Correctly set:"
            printf '%s\n' "${a_output[@]}"
        fi
    fi
}
BASH
    ],
];
