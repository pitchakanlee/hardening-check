<?php
// =============================================================
// == file: CIS_Debian_Linux_12_Benchmark_v1.1.0.pdf
// =============================================================
return [
    // --- 1 Initial Setup ---
    [ 'id' => '1', 'title' => 'Initial Setup', 'type' => 'header' ],

    // --- 1.1 Filesystem ---
    [ 'id' => '1.1', 'title' => 'Filesystem', 'type' => 'header' ],

    // --- 1.1.1 Configure Filesystem Kernel Modules ---
    [ 'id' => '1.1.1', 'title' => 'Configure Filesystem Kernel Modules', 'type' => 'header' ],

    // --- 1.1.1.1 Ensure cramfs kernel module is not available ---
    [ 'id' => '1.1.1.1', 'title' => 'Ensure cramfs kernel module is not available', 'profile' => 'Level 1', 'type' => 'Automated',
      'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
   a_output=()
   a_output2=()
   a_output3=()
   l_dl=""
   l_mod_name="cramfs"
   l_mod_type="fs"
   l_mod_path=$(readlink -f /lib/modules/**/kernel/$l_mod_type | sort -u)

   f_module_chk() {
      l_dl="y"
      a_showconfig=()
      while IFS= read -r l_showconfig; do
         a_showconfig+=("$l_showconfig")
      done < <(modprobe --showconfig | grep -P -- "\b(install|blacklist)\h+${l_mod_chk_name//-/_}\b")

      if ! lsmod | grep -qw "$l_mod_chk_name"; then
         a_output+=("  - kernel module: \"$l_mod_name\" is not loaded")
      else
         a_output2+=("  - kernel module: \"$l_mod_name\" is loaded")
      fi

      if grep -Pq -- "\binstall\h+${l_mod_chk_name//-/_}\h+(\/usr)?\/bin\/(true|false)\b" <<< "${a_showconfig[*]}"; then
         a_output+=("  - kernel module: \"$l_mod_name\" is not loadable")
      else
         a_output2+=("  - kernel module: \"$l_mod_name\" is loadable")
      fi

      if grep -Pq -- "\bblacklist\h+${l_mod_chk_name//-/_}\b" <<< "${a_showconfig[*]}"; then
         a_output+=("  - kernel module: \"$l_mod_name\" is deny listed")
      else
         a_output2+=("  - kernel module: \"$l_mod_name\" is not deny listed")
      fi
   }

   for l_mod_base_directory in $l_mod_path; do
      if [ -d "$l_mod_base_directory/${l_mod_name/-//}" ] && [ -n "$(ls -A "$l_mod_base_directory/${l_mod_name/-//}")" ]; then
         a_output3+=("  - \"$l_mod_base_directory\"")
         l_mod_chk_name="$l_mod_name"
         [[ "$l_mod_name" == overlay* ]] && l_mod_chk_name="${l_mod_name::-2}"
         [ "$l_dl" != "y" ] && f_module_chk
      else
         a_output+=(" - kernel module: \"$l_mod_name\" doesn't exist in \"$l_mod_base_directory\"")
      fi
   done

   if [ "${#a_output3[@]}" -gt 0 ]; then
      printf '%s\n' "" " -- INFO --" " - module: \"$l_mod_name\" exists in:" "${a_output3[@]}"
   fi

   if [ "${#a_output2[@]}" -le 0 ]; then
      printf '%s\n' "" "- Audit Result:" "  ** PASS **" "${a_output[@]}"
   else
      printf '%s\n' "" "- Audit Result:" "  ** FAIL **" " - Reason(s) for audit failure:" "${a_output2[@]}"
      [ "${#a_output[@]}" -gt 0 ] && printf '%s\n' "- Correctly set:" "${a_output[@]}"
   fi
}
BASH
    ],

    // --- 1.1.1.2 Ensure freevxfs kernel module is not available ---
    [ 'id' => '1.1.1.2', 'title' => 'Ensure freevxfs kernel module is not available', 'profile' => 'Level 1', 'type' => 'Automated',
      'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
   a_output=()
   a_output2=()
   a_output3=()
   l_dl=""
   l_mod_name="freevxfs"
   l_mod_type="fs"
   l_mod_path=$(readlink -f /lib/modules/**/kernel/$l_mod_type | sort -u)

   f_module_chk() {
      l_dl="y"
      a_showconfig=()
      while IFS= read -r l_showconfig; do
         a_showconfig+=("$l_showconfig")
      done < <(/usr/sbin/modprobe --showconfig | grep -P -- "\b(install|blacklist)\h+${l_mod_chk_name//-/_}\b")

      if ! lsmod | grep -qw "$l_mod_chk_name"; then
         a_output+=("  - kernel module: \"$l_mod_name\" is not loaded")
      else
         a_output2+=("  - kernel module: \"$l_mod_name\" is loaded")
      fi

      if grep -Pq -- "\binstall\h+${l_mod_chk_name//-/_}\h+(\/usr)?\/bin\/(true|false)\b" <<< "${a_showconfig[*]}"; then
         a_output+=("  - kernel module: \"$l_mod_name\" is not loadable")
      else
         a_output2+=("  - kernel module: \"$l_mod_name\" is loadable")
      fi

      if grep -Pq -- "\bblacklist\h+${l_mod_chk_name//-/_}\b" <<< "${a_showconfig[*]}"; then
         a_output+=("  - kernel module: \"$l_mod_name\" is deny listed")
      else
         a_output2+=("  - kernel module: \"$l_mod_name\" is not deny listed")
      fi
   }

   for l_mod_base_directory in $l_mod_path; do
      mod_dir="$l_mod_base_directory/${l_mod_name/-//}"
      if [ -d "$mod_dir" ] && [ -n "$(ls -A "$mod_dir" 2>/dev/null)" ]; then
         a_output3+=("  - \"$l_mod_base_directory\"")
         l_mod_chk_name="$l_mod_name"
         [[ "$l_mod_name" == overlay* ]] && l_mod_chk_name="${l_mod_name::-2}"
         [ "$l_dl" != "y" ] && f_module_chk
      else
         a_output+=(" - kernel module: \"$l_mod_name\" doesn't exist in \"$l_mod_base_directory\"")
      fi
   done

   if [ "${#a_output3[@]}" -gt 0 ]; then
      printf '%s\n' "" " -- INFO --" " - module: \"$l_mod_name\" exists in:" "${a_output3[@]}"
   fi

   if [ "${#a_output2[@]}" -le 0 ]; then
      printf '%s\n' "" "- Audit Result:" "  ** PASS **" "${a_output[@]}"
   else
      printf '%s\n' "" "- Audit Result:" "  ** FAIL **" " - Reason(s) for audit failure:" "${a_output2[@]}"
      [ "${#a_output[@]}" -gt 0 ] && printf '%s\n' "- Correctly set:" "${a_output[@]}"
   fi
}
BASH
    ],

    // --- 1.1.1.3 Ensure hfs kernel module is not available ---
    [ 'id' => '1.1.1.3', 'title' => 'Ensure hfs kernel module is not available', 'profile' => 'Level 1', 'type' => 'Automated',
      'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
   a_output=()
   a_output2=()
   a_output3=()
   l_dl=""
   l_mod_name="hfs"
   l_mod_type="fs"
   l_mod_path=$(readlink -f /lib/modules/**/kernel/$l_mod_type | sort -u)

   f_module_chk() {
      l_dl="y"
      a_showconfig=()
      while IFS= read -r l_showconfig; do
         a_showconfig+=("$l_showconfig")
      done < <(/usr/sbin/modprobe --showconfig | grep -P -- "\b(install|blacklist)\h+${l_mod_chk_name//-/_}\b")

      if ! lsmod | grep -qw "$l_mod_chk_name"; then
         a_output+=("  - kernel module: \"$l_mod_name\" is not loaded")
      else
         a_output2+=("  - kernel module: \"$l_mod_name\" is loaded")
      fi

      if grep -Pq -- "\binstall\h+${l_mod_chk_name//-/_}\h+(\/usr)?\/bin\/(true|false)\b" <<< "${a_showconfig[*]}"; then
         a_output+=("  - kernel module: \"$l_mod_name\" is not loadable")
      else
         a_output2+=("  - kernel module: \"$l_mod_name\" is loadable")
      fi

      if grep -Pq -- "\bblacklist\h+${l_mod_chk_name//-/_}\b" <<< "${a_showconfig[*]}"; then
         a_output+=("  - kernel module: \"$l_mod_name\" is deny listed")
      else
         a_output2+=("  - kernel module: \"$l_mod_name\" is not deny listed")
      fi
   }

   for l_mod_base_directory in $l_mod_path; do
      mod_dir="$l_mod_base_directory/${l_mod_name/-//}"
      if [ -d "$mod_dir" ] && [ -n "$(ls -A "$mod_dir" 2>/dev/null)" ]; then
         a_output3+=("  - \"$l_mod_base_directory\"")
         l_mod_chk_name="$l_mod_name"
         [[ "$l_mod_name" == overlay* ]] && l_mod_chk_name="${l_mod_name::-2}"
         [ "$l_dl" != "y" ] && f_module_chk
      else
         a_output+=(" - kernel module: \"$l_mod_name\" doesn't exist in \"$l_mod_base_directory\"")
      fi
   done

   if [ "${#a_output3[@]}" -gt 0 ]; then
      printf '%s\n' "" " -- INFO --" " - module: \"$l_mod_name\" exists in:" "${a_output3[@]}"
   fi

   if [ "${#a_output2[@]}" -le 0 ]; then
      printf '%s\n' "" "- Audit Result:" "  ** PASS **" "${a_output[@]}"
   else
      printf '%s\n' "" "- Audit Result:" "  ** FAIL **" " - Reason(s) for audit failure:" "${a_output2[@]}"
      [ "${#a_output[@]}" -gt 0 ] && printf '%s\n' "- Correctly set:" "${a_output[@]}"
   fi
}
BASH
    ],

    // --- 1.1.1.4 Ensure hfsplus kernel module is not available ---
    [ 'id' => '1.1.1.4', 'title' => 'Ensure hfsplus kernel module is not available', 'profile' => 'Level 1', 'type' => 'Automated',
      'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
   a_output=()
   a_output2=()
   a_output3=()
   l_dl=""
   l_mod_name="hfsplus"
   l_mod_type="fs"
   l_mod_path=$(readlink -f /lib/modules/**/kernel/$l_mod_type | sort -u)

   f_module_chk() {
      l_dl="y"
      a_showconfig=()
      while IFS= read -r l_showconfig; do
         a_showconfig+=("$l_showconfig")
      done < <(/usr/sbin/modprobe --showconfig | grep -P -- "\b(install|blacklist)\h+${l_mod_chk_name//-/_}\b")

      if ! lsmod | grep -qw "$l_mod_chk_name"; then
         a_output+=("  - kernel module: \"$l_mod_name\" is not loaded")
      else
         a_output2+=("  - kernel module: \"$l_mod_name\" is loaded")
      fi

      if grep -Pq -- "\binstall\h+${l_mod_chk_name//-/_}\h+(\/usr)?\/bin\/(true|false)\b" <<< "${a_showconfig[*]}"; then
         a_output+=("  - kernel module: \"$l_mod_name\" is not loadable")
      else
         a_output2+=("  - kernel module: \"$l_mod_name\" is loadable")
      fi

      if grep -Pq -- "\bblacklist\h+${l_mod_chk_name//-/_}\b" <<< "${a_showconfig[*]}"; then
         a_output+=("  - kernel module: \"$l_mod_name\" is deny listed")
      else
         a_output2+=("  - kernel module: \"$l_mod_name\" is not deny listed")
      fi
   }

   for l_mod_base_directory in $l_mod_path; do
      mod_dir="$l_mod_base_directory/${l_mod_name/-//}"
      if [ -d "$mod_dir" ] && [ -n "$(ls -A "$mod_dir" 2>/dev/null)" ]; then
         a_output3+=("  - \"$l_mod_base_directory\"")
         l_mod_chk_name="$l_mod_name"
         [[ "$l_mod_name" == overlay* ]] && l_mod_chk_name="${l_mod_name::-2}"
         [ "$l_dl" != "y" ] && f_module_chk
      else
         a_output+=(" - kernel module: \"$l_mod_name\" doesn't exist in \"$l_mod_base_directory\"")
      fi
   done

   if [ "${#a_output3[@]}" -gt 0 ]; then
      printf '%s\n' "" " -- INFO --" " - module: \"$l_mod_name\" exists in:" "${a_output3[@]}"
   fi

   if [ "${#a_output2[@]}" -le 0 ]; then
      printf '%s\n' "" "- Audit Result:" "  ** PASS **" "${a_output[@]}"
   else
      printf '%s\n' "" "- Audit Result:" "  ** FAIL **" " - Reason(s) for audit failure:" "${a_output2[@]}"
      [ "${#a_output[@]}" -gt 0 ] && printf '%s\n' "- Correctly set:" "${a_output[@]}"
   fi
}

BASH
    ],

    // --- 1.1.1.5 Ensure jffs2 kernel module is not available ---
    [ 'id' => '1.1.1.5', 'title' => 'Ensure jffs2 kernel module is not available', 'profile' => 'Level 1', 'type' => 'Automated',
      'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
   a_output=()
   a_output2=()
   a_output3=()
   l_dl=""
   l_mod_name="jffs2"
   l_mod_type="fs"
   l_mod_path=$(readlink -f /lib/modules/**/kernel/$l_mod_type | sort -u)

   f_module_chk() {
      l_dl="y"
      a_showconfig=()
      while IFS= read -r l_showconfig; do
         a_showconfig+=("$l_showconfig")
      done < <(/usr/sbin/modprobe --showconfig | grep -P -- "\b(install|blacklist)\h+${l_mod_chk_name//-/_}\b")

      if ! lsmod | grep -qw "$l_mod_chk_name"; then
         a_output+=("  - kernel module: \"$l_mod_name\" is not loaded")
      else
         a_output2+=("  - kernel module: \"$l_mod_name\" is loaded")
      fi

      if grep -Pq -- "\binstall\h+${l_mod_chk_name//-/_}\h+(\/usr)?\/bin\/(true|false)\b" <<< "${a_showconfig[*]}"; then
         a_output+=("  - kernel module: \"$l_mod_name\" is not loadable")
      else
         a_output2+=("  - kernel module: \"$l_mod_name\" is loadable")
      fi

      if grep -Pq -- "\bblacklist\h+${l_mod_chk_name//-/_}\b" <<< "${a_showconfig[*]}"; then
         a_output+=("  - kernel module: \"$l_mod_name\" is deny listed")
      else
         a_output2+=("  - kernel module: \"$l_mod_name\" is not deny listed")
      fi
   }

   for l_mod_base_directory in $l_mod_path; do
      mod_dir="$l_mod_base_directory/${l_mod_name/-//}"
      if [ -d "$mod_dir" ] && [ -n "$(ls -A "$mod_dir" 2>/dev/null)" ]; then
         a_output3+=("  - \"$l_mod_base_directory\"")
         l_mod_chk_name="$l_mod_name"
         [[ "$l_mod_name" == overlay* ]] && l_mod_chk_name="${l_mod_name::-2}"
         [ "$l_dl" != "y" ] && f_module_chk
      else
         a_output+=(" - kernel module: \"$l_mod_name\" doesn't exist in \"$l_mod_base_directory\"")
      fi
   done

   if [ "${#a_output3[@]}" -gt 0 ]; then
      printf '%s\n' "" " -- INFO --" " - module: \"$l_mod_name\" exists in:" "${a_output3[@]}"
   fi

   if [ "${#a_output2[@]}" -le 0 ]; then
      printf '%s\n' "" "- Audit Result:" "  ** PASS **" "${a_output[@]}"
   else
      printf '%s\n' "" "- Audit Result:" "  ** FAIL **" " - Reason(s) for audit failure:" "${a_output2[@]}"
      [ "${#a_output[@]}" -gt 0 ] && printf '%s\n' "- Correctly set:" "${a_output[@]}"
   fi
}
BASH
    ],

    // --- 1.1.1.6 Ensure overlayfs kernel module is not available ---
    [ 'id' => '1.1.1.6', 'title' => 'Ensure overlayfs kernel module is not available', 'profile' => 'Level 2', 'type' => 'Automated',
      'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
   a_output=()
   a_output2=()
   a_output3=()
   l_dl=""
   l_mod_name="overlayfs"
   l_mod_type="fs"
   l_mod_path=$(readlink -f /lib/modules/**/kernel/$l_mod_type 2>/dev/null | sort -u)

   f_module_chk() {
      l_dl="y"
      a_showconfig=()
      while IFS= read -r l_showconfig; do
         a_showconfig+=("$l_showconfig")
      done < <(/usr/sbin/modprobe --showconfig 2>/dev/null | grep -P -- "\b(install|blacklist)\h+${l_mod_chk_name//-/_}\b")

      if ! lsmod | grep -qw "$l_mod_chk_name"; then
         a_output+=("  - kernel module: \"$l_mod_name\" is not loaded")
      else
         a_output2+=("  - kernel module: \"$l_mod_name\" is loaded")
      fi

      if grep -Pq -- "\binstall\h+${l_mod_chk_name//-/_}\h+(\/usr)?\/bin\/(true|false)\b" <<< "${a_showconfig[*]}"; then
         a_output+=("  - kernel module: \"$l_mod_name\" is not loadable")
      else
         a_output2+=("  - kernel module: \"$l_mod_name\" is loadable")
      fi

      if grep -Pq -- "\bblacklist\h+${l_mod_chk_name//-/_}\b" <<< "${a_showconfig[*]}"; then
         a_output+=("  - kernel module: \"$l_mod_name\" is deny listed")
      else
         a_output2+=("  - kernel module: \"$l_mod_name\" is not deny listed")
      fi
   }

   for l_mod_base_directory in $l_mod_path; do
      mod_dir="$l_mod_base_directory/${l_mod_name/-//}"
      if [ -d "$mod_dir" ] && [ -n "$(ls -A "$mod_dir" 2>/dev/null)" ]; then
         a_output3+=("  - \"$l_mod_base_directory\"")
         l_mod_chk_name="$l_mod_name"
         [[ "$l_mod_name" == overlay* ]] && l_mod_chk_name="overlay"
         [ "$l_dl" != "y" ] && f_module_chk
      else
         a_output+=(" - kernel module: \"$l_mod_name\" doesn't exist in \"$l_mod_base_directory\"")
      fi
   done

   if [ "${#a_output3[@]}" -gt 0 ]; then
      printf '%s\n' "" " -- INFO --" " - module: \"$l_mod_name\" exists in:" "${a_output3[@]}"
   fi

   if [ "${#a_output2[@]}" -le 0 ]; then
      printf '%s\n' "" "- Audit Result:" "  ** PASS **" "${a_output[@]}"
   else
      printf '%s\n' "" "- Audit Result:" "  ** FAIL **" " - Reason(s) for audit failure:" "${a_output2[@]}"
      [ "${#a_output[@]}" -gt 0 ] && printf '%s\n' "- Correctly set:" "${a_output[@]}"
   fi
}
BASH
    ],

    // --- 1.1.1.7 Ensure squashfs kernel module is not available ---
    [ 'id' => '1.1.1.7', 'title' => 'Ensure squashfs kernel module is not available', 'profile' => 'Level 2', 'type' => 'Automated',
      'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
   a_output=()
   a_output2=()
   a_output3=()
   l_dl=""
   l_mod_name="squashfs"
   l_mod_type="fs"
   l_mod_path=$(readlink -f /lib/modules/**/kernel/$l_mod_type 2>/dev/null | sort -u)

   f_module_chk() {
      l_dl="y"
      a_showconfig=()
      while IFS= read -r l_showconfig; do
         a_showconfig+=("$l_showconfig")
      done < <(/usr/sbin/modprobe --showconfig 2>/dev/null | grep -P -- "\b(install|blacklist)\h+${l_mod_chk_name//-/_}\b")

      if ! lsmod | grep -qw "$l_mod_chk_name"; then
         a_output+=("  - kernel module: \"$l_mod_name\" is not loaded")
      else
         a_output2+=("  - kernel module: \"$l_mod_name\" is loaded")
      fi

      if grep -Pq -- "\binstall\h+${l_mod_chk_name//-/_}\h+(\/usr)?\/bin\/(true|false)\b" <<< "${a_showconfig[*]}"; then
         a_output+=("  - kernel module: \"$l_mod_name\" is not loadable")
      else
         a_output2+=("  - kernel module: \"$l_mod_name\" is loadable")
      fi

      if grep -Pq -- "\bblacklist\h+${l_mod_chk_name//-/_}\b" <<< "${a_showconfig[*]}"; then
         a_output+=("  - kernel module: \"$l_mod_name\" is deny listed")
      else
         a_output2+=("  - kernel module: \"$l_mod_name\" is not deny listed")
      fi
   }

   for l_mod_base_directory in $l_mod_path; do
      mod_dir="$l_mod_base_directory/${l_mod_name/-//}"
      if [ -d "$mod_dir" ] && [ -n "$(ls -A "$mod_dir" 2>/dev/null)" ]; then
         a_output3+=("  - \"$l_mod_base_directory\"")
         l_mod_chk_name="$l_mod_name"
         [[ "$l_mod_name" == overlay* ]] && l_mod_chk_name="overlay"
         [ "$l_dl" != "y" ] && f_module_chk
      else
         a_output+=(" - kernel module: \"$l_mod_name\" doesn't exist in \"$l_mod_base_directory\"")
      fi
   done

   if [ "${#a_output3[@]}" -gt 0 ]; then
      printf '%s\n' "" " -- INFO --" " - module: \"$l_mod_name\" exists in:" "${a_output3[@]}"
   fi

   if [ "${#a_output2[@]}" -le 0 ]; then
      printf '%s\n' "" "- Audit Result:" "  ** PASS **" "${a_output[@]}"
   else
      printf '%s\n' "" "- Audit Result:" "  ** FAIL **" " - Reason(s) for audit failure:" "${a_output2[@]}"
      [ "${#a_output[@]}" -gt 0 ] && printf '%s\n' "- Correctly set:" "${a_output[@]}"
   fi
}
BASH
    ],

    // --- 1.1.1.8 Ensure udf kernel module is not available ---
    [ 'id' => '1.1.1.8', 'title' => 'Ensure udf kernel module is not available', 'profile' => 'Level 1', 'type' => 'Automated',
      'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
   a_output=()
   a_output2=()
   a_output3=()
   l_dl=""
   l_mod_name="udf"
   l_mod_type="fs"
   l_mod_path=$(readlink -f /lib/modules/**/kernel/$l_mod_type 2>/dev/null | sort -u)

   f_module_chk() {
      l_dl="y"
      a_showconfig=()
      while IFS= read -r l_showconfig; do
         a_showconfig+=("$l_showconfig")
      done < <(modprobe --showconfig 2>/dev/null | grep -P -- "\b(install|blacklist)\h+${l_mod_chk_name//-/_}\b")

      if ! lsmod | grep -qw "$l_mod_chk_name"; then
         a_output+=("  - kernel module: \"$l_mod_name\" is not loaded")
      else
         a_output2+=("  - kernel module: \"$l_mod_name\" is loaded")
      fi

      if grep -Pq -- "\binstall\h+${l_mod_chk_name//-/_}\h+(\/usr)?\/bin\/(true|false)\b" <<< "${a_showconfig[*]}"; then
         a_output+=("  - kernel module: \"$l_mod_name\" is not loadable")
      else
         a_output2+=("  - kernel module: \"$l_mod_name\" is loadable")
      fi

      if grep -Pq -- "\bblacklist\h+${l_mod_chk_name//-/_}\b" <<< "${a_showconfig[*]}"; then
         a_output+=("  - kernel module: \"$l_mod_name\" is deny listed")
      else
         a_output2+=("  - kernel module: \"$l_mod_name\" is not deny listed")
      fi
   }

   for l_mod_base_directory in $l_mod_path; do
      if [ -d "$l_mod_base_directory/${l_mod_name/-/\/}" ] && [ -n "$(ls -A "$l_mod_base_directory/${l_mod_name/-/\/}")" ]; then
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

    // --- 1.1.1.9 Ensure usb-storage kernel module is not available ---
    [ 'id' => '1.1.1.9', 'title' => 'Ensure usb-storage kernel module is not available', 'profile' => 'Level 1', 'type' => 'Automated',
      'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
   a_output=()
   a_output2=()
   a_output3=()
   l_dl=""
   l_mod_name="usb-storage"
   l_mod_type="drivers"
   l_mod_path=$(readlink -f /lib/modules/**/kernel/$l_mod_type 2>/dev/null | sort -u)

   f_module_chk() {
      l_dl="y"
      a_showconfig=()
      while IFS= read -r l_showconfig; do
         a_showconfig+=("$l_showconfig")
      done < <(modprobe --showconfig 2>/dev/null | grep -P -- "\b(install|blacklist)\h+${l_mod_chk_name//-/_}\b")

      if ! lsmod | grep -qw "$l_mod_chk_name"; then
         a_output+=("  - kernel module: \"$l_mod_name\" is not loaded")
      else
         a_output2+=("  - kernel module: \"$l_mod_name\" is loaded")
      fi

      if grep -Pq -- "\binstall\h+${l_mod_chk_name//-/_}\h+(\/usr)?\/bin\/(true|false)\b" <<< "${a_showconfig[*]}"; then
         a_output+=("  - kernel module: \"$l_mod_name\" is not loadable")
      else
         a_output2+=("  - kernel module: \"$l_mod_name\" is loadable")
      fi

      if grep -Pq -- "\bblacklist\h+${l_mod_chk_name//-/_}\b" <<< "${a_showconfig[*]}"; then
         a_output+=("  - kernel module: \"$l_mod_name\" is deny listed")
      else
         a_output2+=("  - kernel module: \"$l_mod_name\" is not deny listed")
      fi
   }

   for l_mod_base_directory in $l_mod_path; do
      if [ -d "$l_mod_base_directory/${l_mod_name/-/\/}" ] && [ -n "$(ls -A "$l_mod_base_directory/${l_mod_name/-/\/}")" ]; then
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

    // --- 1.1.1.10 Ensure unused filesystems kernel modules are not available ---
    [ 'id' => '1.1.1.10', 'title' => 'Ensure unused filesystems kernel modules are not available', 'profile' => 'Level 2', 'type' => 'Automated',
      'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
   a_output=()
   a_output2=()
   a_modprope_config=()
   a_excluded=()
   a_available_modules=()
   a_ignore=("xfs" "vfat" "ext2" "ext3" "ext4")
   a_cve_exists=("afs" "ceph" "cifs" "exfat" "fat" "fscache" "fuse" "gfs2")

   f_module_chk() {
      local l_out2=""
      grep -Pq -- "\b$l_mod_name\b" <<< "${a_cve_exists[*]}" && l_out2=" <- CVE exists!"
      if ! grep -Pq -- '\bblacklist\h+'"$l_mod_name"'\b' <<< "${a_modprope_config[*]}"; then
         a_output2+=("  - Kernel module: \"$l_mod_name\" is not fully disabled$l_out2")
      elif ! grep -Pq -- '\binstall\h+'"$l_mod_name"'\h+(\/usr)?\/bin\/(false|true)\b' <<< "${a_modprope_config[*]}"; then
         a_output2+=("  - Kernel module: \"$l_mod_name\" is not fully disabled$l_out2")
      fi
      if lsmod | grep -qw "$l_mod_name"; then # Check if module is loaded
         a_output2+=("  - Kernel module: \"$l_mod_name\" is loaded")
      fi
   }

   while IFS= read -r -d $'\0' l_module_dir; do
      a_available_modules+=("$(basename "$l_module_dir")")
   done < <(find "$(readlink -f /lib/modules/"$(uname -r)"/kernel/fs)" -mindepth 1 -maxdepth 1 -type d ! -empty -print0)

   while IFS= read -r l_exclude; do
      if grep -Pq -- "\b$l_exclude\b" <<< "${a_cve_exists[*]}"; then
         a_output2+=("  - ** WARNING: kernel module: \"$l_exclude\" has a CVE and is currently mounted! **")
      elif grep -Pq -- "\b$l_exclude\b" <<< "${a_available_modules[*]}"; then
         a_output+=("  - Kernel module: \"$l_exclude\" is currently mounted - do NOT unload or disable")
      fi
      if ! grep -Pq -- "\b$l_exclude\b" <<< "${a_ignore[*]}"; then
         a_ignore+=("$l_exclude")
      fi
   done < <(findmnt -knD | awk '{print $2}' | sort -u)

   while IFS= read -r l_config; do
      a_modprope_config+=("$l_config")
   done < <(modprobe --showconfig 2>/dev/null | grep -P '^\h*(blacklist|install)')

   for l_mod_name in "${a_available_modules[@]}"; do
      [[ "$l_mod_name" =~ overlay ]] && l_mod_name="${l_mod_name::-2}"
      if grep -Pq -- "\b$l_mod_name\b" <<< "${a_ignore[*]}"; then
         a_excluded+=(" - Kernel module: \"$l_mod_name\"")
      else
         f_module_chk
      fi
   done

   if [ "${#a_excluded[@]}" -gt 0 ]; then
      printf '%s\n\n' " -- INFO --" "The following intentionally skipped:" "${a_excluded[@]}"
   fi

   if [ "${#a_output2[@]}" -le 0 ]; then
      printf '%s\n\n' "  - No unused filesystem kernel modules are enabled" "${a_output[@]}"
   else
      printf '%s\n\n' "-- Audit Result: --" "  ** REVIEW the following **" "${a_output2[@]}"
      if [ "${#a_output[@]}" -gt 0 ]; then
         printf '%s\n\n' "-- Correctly set: --" "${a_output[@]}"
      fi
   fi
}
BASH
    ],

    // --- 1.1.2 Configure Filesystem Paritions ---
    [ 'id' => '1.1.2', 'title' => 'Configure Filesystem Paritions', 'type' => 'header' ],

    // --- 1.1.2.1 Configure /tmp ---
    [ 'id' => '1.1.2.1', 'title' => 'Configure /tmp', 'type' => 'header' ],

    // --- 1.1.2.1.1 Ensure /tmp is a separate partition ---
    [ 'id' => '1.1.2.1.1', 'title' => 'Ensure /tmp is a separate partition', 'profile' => 'Level 1', 'type' => 'Automated',
      'requires_root' => false, 'audit_script' => <<<'BASH'
if findmnt -kn /tmp >/dev/null; then echo "** PASS **"; else echo "** FAIL **"; fi
BASH
    ],

    // --- 1.1.2.1.2 Ensure nodev option set on /tmp partition ---
    [ 'id' => '1.1.2.1.2', 'title' => 'Ensure nodev option set on /tmp partition', 'profile' => 'Level 1', 'type' => 'Automated',
      'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output=()
    a_output2=()
    a_output_info=()
    if findmnt -kn /tmp >/dev/null 2>&1; then
        a_output+=("  - /tmp is a separate mount point.")
        a_output_info+=("  - Mount details: $(findmnt -kn /tmp)")
        if findmnt -kn /tmp | grep -q 'nodev'; then
            a_output+=("  - /tmp is mounted with the 'nodev' option.")
        else
            a_output2+=("  - /tmp is NOT mounted with the 'nodev' option.")
        fi
    else
        a_output2+=("  - /tmp is NOT a separate mount point.")
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi
    if [ "${#a_output2[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
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

    // --- 1.1.2.1.3 Ensure nosuid option set on /tmp partition ---
    [ 'id' => '1.1.2.1.3', 'title' => 'Ensure nosuid option set on /tmp partition', 'profile' => 'Level 1', 'type' => 'Automated',
      'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output=()
    a_output2=()
    a_output_info=()

    if findmnt -kn /tmp >/dev/null 2>&1; then
        a_output+=("  - /tmp is a separate mount point.")
        a_output_info+=("  - Mount details: $(findmnt -kn /tmp)")
        if findmnt -kn /tmp | grep -q 'nosuid'; then
            a_output+=("  - /tmp is mounted with the 'nosuid' option.")
        else
            a_output2+=("  - /tmp is NOT mounted with the 'nosuid' option.")
        fi
    else
        a_output2+=("  - /tmp is NOT a separate mount point.")
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output2[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
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

    // --- 1.1.2.1.4 Ensure noexec option set on /tmp partition ---
    [ 'id' => '1.1.2.1.4', 'title' => 'Ensure noexec option set on /tmp partition', 'profile' => 'Level 1', 'type' => 'Automated',
      'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output=()
    a_output2=()
    a_output_info=()
    if findmnt -kn /tmp >/dev/null 2>&1; then
        a_output+=("  - /tmp is a separate mount point.")
        a_output_info+=("  - Mount details: $(findmnt -kn /tmp)")

        if findmnt -kn /tmp | grep -q 'noexec'; then
            a_output+=("  - /tmp is mounted with the 'noexec' option.")
        else
            a_output2+=("  - /tmp is NOT mounted with the 'noexec' option.")
        fi
    else
        a_output2+=("  - /tmp is NOT a separate mount point.")
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output2[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
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

    // --- 1.1.2.2 Configure /dev/shm ---
    [ 'id' => '1.1.2.2', 'title' => 'Configure /dev/shm', 'type' => 'header' ],

    // --- 1.1.2.2.1 Ensure /dev/shm is a separate partition ---
    [ 'id' => '1.1.2.2.1', 'title' => 'Ensure /dev/shm is a separate partition', 'profile' => 'Level 1', 'type' => 'Automated',
      'requires_root' => false, 'audit_script' => <<<'BASH'
if findmnt -kn /dev/shm >/dev/null; then echo "** PASS **"; else echo "** FAIL **"; fi
BASH
    ],

    // --- 1.1.2.2.2 Ensure nodev option set on /dev/shm partition ---
    [ 'id' => '1.1.2.2.2', 'title' => 'Ensure nodev option set on /dev/shm partition', 'profile' => 'Level 1', 'type' => 'Automated',
      'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output=()
    a_output2=()
    a_output_info=()

    if findmnt -kn /dev/shm >/dev/null 2>&1; then
        a_output+=("  - /dev/shm is a separate mount point.")
        a_output_info+=("  - Mount details: $(findmnt -kn /dev/shm)")

        if findmnt -kn /dev/shm | grep -q 'nodev'; then
            a_output+=("  - /dev/shm is mounted with the 'nodev' option.")
        else
            a_output2+=("  - /dev/shm is NOT mounted with the 'nodev' option.")
        fi
    else
        a_output2+=("  - /dev/shm is NOT a separate mount point.")
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output2[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
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

    // --- 1.1.2.2.3 Ensure nosuid option set on /dev/shm partition ---
    [ 'id' => '1.1.2.2.3', 'title' => 'Ensure nosuid option set on /dev/shm partition', 'profile' => 'Level 1', 'type' => 'Automated',
      'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output=()
    a_output2=()
    a_output_info=()

    if findmnt -kn /dev/shm >/dev/null 2>&1; then
        a_output+=("  - /dev/shm is a separate mount point.")
        a_output_info+=("  - Mount details: $(findmnt -kn /dev/shm)")

        if findmnt -kn /dev/shm | grep -q 'nosuid'; then
            a_output+=("  - /dev/shm is mounted with the 'nosuid' option.")
        else
            a_output2+=("  - /dev/shm is NOT mounted with the 'nosuid' option.")
        fi
    else
        a_output2+=("  - /dev/shm is NOT a separate mount point.")
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output2[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
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

    // --- 1.1.2.2.4 Ensure noexec option set on /dev/shm partition ---
    [ 'id' => '1.1.2.2.4', 'title' => 'Ensure noexec option set on /dev/shm partition', 'profile' => 'Level 1', 'type' => 'Automated',
      'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output=()
    a_output2=()
    a_output_info=()

    if findmnt -kn /dev/shm >/dev/null 2>&1; then
        a_output+=("  - /dev/shm is a separate mount point.")
        a_output_info+=("  - Mount details: $(findmnt -kn /dev/shm)")

        if findmnt -kn /dev/shm | grep -q 'noexec'; then
            a_output+=("  - /dev/shm is mounted with the 'noexec' option.")
        else
            a_output2+=("  - /dev/shm is NOT mounted with the 'noexec' option.")
        fi
    else
        a_output2+=("  - /dev/shm is NOT a separate mount point.")
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output2[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
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

    // --- 1.1.2.3 Configure /home ---
    [ 'id' => '1.1.2.3', 'title' => 'Configure /home', 'type' => 'header' ],

    // --- 1.1.2.3.1 Ensure /home is a separate partition ---
    [ 'id' => '1.1.2.3.1', 'title' => 'Ensure /home is a separate partition', 'profile' => 'Level 1', 'type' => 'Automated',
      'requires_root' => false, 'audit_script' => <<<'BASH'
if findmnt -kn /home >/dev/null; then echo "** PASS **"; else echo "** FAIL **"; fi
BASH
    ],

    // --- 1.1.2.3.2 Ensure nodev option set on /home partition ---
    [ 'id' => '1.1.2.3.2', 'title' => 'Ensure nodev option set on /home partition', 'profile' => 'Level 1', 'type' => 'Automated',
      'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output=()
    a_output2=()
    a_output_info=()

    if findmnt -kn /home >/dev/null 2>&1; then
        a_output+=("  - /home is a separate mount point.")
        a_output_info+=("  - Mount details: $(findmnt -kn /home)")

        if findmnt -kn /home | grep -q 'nodev'; then
            a_output+=("  - /home is mounted with the 'nodev' option.")
        else
            a_output2+=("  - /home is NOT mounted with the 'nodev' option.")
        fi
    else
        a_output2+=("  - /home is NOT a separate mount point.")
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output2[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
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

    // --- 1.1.2.3.3 Ensure nosuid option set on /home partition ---
    [
        'id' => '1.1.2.3.3', 'title' => 'Ensure nosuid option set on /home partition', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output=()
    a_output2=()
    a_output_info=()

    if findmnt -kn /home >/dev/null 2>&1; then
        a_output+=("  - /home is a separate mount point.")
        a_output_info+=("  - Mount details: $(findmnt -kn /home)")

        if findmnt -kn /home | grep -q 'nosuid'; then
            a_output+=("  - /home is mounted with the 'nosuid' option.")
        else
            a_output2+=("  - /home is NOT mounted with the 'nosuid' option.")
        fi
    else
        a_output2+=("  - /home is NOT a separate mount point.")
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi
    if [ "${#a_output2[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
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

    // --- 1.1.2.4 Configure /var ---
    [ 'id' => '1.1.2.4', 'title' => 'Configure /var', 'type' => 'header' ], 

    // --- 1.1.2.4.1 Ensure /var is a separate partition ---
    [
        'id' => '1.1.2.4.1', 'title' => 'Ensure /var is a separate partition', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
if findmnt -kn /var >/dev/null; then echo "** PASS **"; else echo "** FAIL **"; fi
BASH
    ],

    // --- 1.1.2.4.2 Ensure nodev option set on /var partition ---
    [
        'id' => '1.1.2.4.2', 'title' => 'Ensure nodev option set on /var partition', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output=()
    a_output2=()
    a_output_info=()

    if findmnt -kn /var >/dev/null 2>&1; then
        a_output+=("  - /var is a separate mount point.")
        a_output_info+=("  - Mount details: $(findmnt -kn /var)")

        if findmnt -kn /var | grep -q 'nodev'; then
            a_output+=("  - /var is mounted with the 'nodev' option.")
        else
            a_output2+=("  - /var is NOT mounted with the 'nodev' option.")
        fi
    else
        a_output2+=("  - /var is NOT a separate mount point.")
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output2[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
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

    // --- 1.1.2.4.3 Ensure nosuid option set on /var partition ---
    [
        'id' => '1.1.2.4.3', 'title' => 'Ensure nosuid option set on /var partition', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output=()
    a_output2=()
    a_output_info=()

    if findmnt -kn /var >/dev/null 2>&1; then
        a_output+=("  - /var is a separate mount point.")
        a_output_info+=("  - Mount details: $(findmnt -kn /var)")

        if findmnt -kn /var | grep -q 'nosuid'; then
            a_output+=("  - /var is mounted with the 'nosuid' option.")
        else
            a_output2+=("  - /var is NOT mounted with the 'nosuid' option.")
        fi
    else
        a_output2+=("  - /var is NOT a separate mount point.")
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi
    if [ "${#a_output2[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
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

    // --- 1.1.2.5 Configure /var/tmp ---
    [ 'id' => '1.1.2.1', 'title' => 'Configure /var/tmp', 'type' => 'header' ],

    // --- 1.1.2.5.1 Ensure /var/tmp is a separate partition ---
    [
        'id' => '1.1.2.5.1', 'title' => 'Ensure /var/tmp is a separate partition', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
if findmnt -kn /var/tmp >/dev/null; then echo "** PASS **"; else echo "** FAIL **"; fi
BASH
    ],

    // --- 1.1.2.5.2 Ensure nodev option set on /var/tmp partition ---
    [
        'id' => '1.1.2.5.2', 'title' => 'Ensure nodev option set on /var/tmp partition', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output=()
    a_output2=()
    a_output_info=()

    if findmnt -kn /var/tmp >/dev/null 2>&1; then
        a_output+=("  - /var/tmp is a separate mount point.")
        a_output_info+=("  - Mount details: $(findmnt -kn /var/tmp)")

        if findmnt -kn /var/tmp | grep -q 'nodev'; then
            a_output+=("  - /var/tmp is mounted with the 'nodev' option.")
        else
            a_output2+=("  - /var/tmp is NOT mounted with the 'nodev' option.")
        fi
    else
        a_output2+=("  - /var/tmp is NOT a separate mount point.")
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output2[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
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

    // --- 1.1.2.5.3 Ensure nosuid option set on /var/tmp partition ---
    [
        'id' => '1.1.2.5.3', 'title' => 'Ensure nosuid option set on /var/tmp partition', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output=()
    a_output2=()
    a_output_info=()

    if findmnt -kn /var/tmp >/dev/null 2>&1; then
        a_output+=("  - /var/tmp is a separate mount point.")
        a_output_info+=("  - Mount details: $(findmnt -kn /var/tmp)")

        if findmnt -kn /var/tmp | grep -q 'nosuid'; then
            a_output+=("  - /var/tmp is mounted with the 'nosuid' option.")
        else
            a_output2+=("  - /var/tmp is NOT mounted with the 'nosuid' option.")
        fi
    else
        a_output2+=("  - /var/tmp is NOT a separate mount point.")
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi
    if [ "${#a_output2[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
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

    // --- 1.1.2.5.4 Ensure noexec option set on /var/tmp partition ---
    [
        'id' => '1.1.2.5.4', 'title' => 'Ensure noexec option set on /var/tmp partition', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output=()
    a_output2=()
    a_output_info=()

    if findmnt -kn /var/tmp >/dev/null 2>&1; then
        a_output+=("  - /var/tmp is a separate mount point.")
        a_output_info+=("  - Mount details: $(findmnt -kn /var/tmp)")

        if findmnt -kn /var/tmp | grep -q 'noexec'; then
            a_output+=("  - /var/tmp is mounted with the 'noexec' option.")
        else
            a_output2+=("  - /var/tmp is NOT mounted with the 'noexec' option.")
        fi
    else
        a_output2+=("  - /var/tmp is NOT a separate mount point.")
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output2[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
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
    // --- 1.1.2.6 Configure /var/log ---
    [ 'id' => '1.1.2.6', 'title' => 'Configure /var/log', 'type' => 'header' ],

    // --- 1.1.2.6.1 Ensure /var/log is a separate partition ---
    [
        'id' => '1.1.2.6.1', 'title' => 'Ensure /var/log is a separate partition', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
if findmnt -kn /var/log >/dev/null; then echo "** PASS **"; else echo "** FAIL **"; fi
BASH
    ],

    // --- 1.1.2.6.2 Ensure nodev option set on /var/log partition ---
    [
        'id' => '1.1.2.6.2', 'title' => 'Ensure nodev option set on /var/log partition', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output=()
    a_output2=()
    a_output_info=()

    if findmnt -kn /var/log >/dev/null 2>&1; then
        a_output+=("  - /var/log is a separate mount point.")
        a_output_info+=("  - Mount details: $(findmnt -kn /var/log)")

        if findmnt -kn /var/log | grep -q 'nodev'; then
            a_output+=("  - /var/log is mounted with the 'nodev' option.")
        else
            a_output2+=("  - /var/log is NOT mounted with the 'nodev' option.")
        fi
    else
        a_output2+=("  - /var/log is NOT a separate mount point.")
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output2[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
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

    // --- 1.1.2.6.3 Ensure nosuid option set on /var/log partition ---
    [
        'id' => '1.1.2.6.3', 'title' => 'Ensure nosuid option set on /var/log partition', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output=()
    a_output2=()
    a_output_info=()

    if findmnt -kn /var/log >/dev/null 2>&1; then
        a_output+=("  - /var/log is a separate mount point.")
        a_output_info+=("  - Mount details: $(findmnt -kn /var/log)")
        if findmnt -kn /var/log | grep -q 'nosuid'; then
            a_output+=("  - /var/log is mounted with the 'nosuid' option.")
        else
            a_output2+=("  - /var/log is NOT mounted with the 'nosuid' option.")
        fi
    else
        a_output2+=("  - /var/log is NOT a separate mount point.")
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi
    if [ "${#a_output2[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
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

    // --- 1.1.2.6.4 Ensure noexec option set on /var/log partition ---
    [
        'id' => '1.1.2.6.4', 'title' => 'Ensure noexec option set on /var/log partition', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output=()
    a_output2=()
    a_output_info=()

    if findmnt -kn /var/log >/dev/null 2>&1; then
        a_output+=("  - /var/log is a separate mount point.")
        a_output_info+=("  - Mount details: $(findmnt -kn /var/log)")

        if findmnt -kn /var/log | grep -q 'noexec'; then
           a_output+=("  - /var/log is mounted with the 'noexec' option.")
        else
           a_output2+=("  - /var/log is NOT mounted with the 'noexec' option.")
        fi
    else
        a_output2+=("  - /var/log is NOT a separate mount point.")
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output2[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
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

    // --- 1.1.2.7 Configure /var/log/audit ---
    [ 'id' => '1.1.2.7', 'title' => 'Configure /var/log/audit', 'type' => 'header' ],

    // --- 1.1.2.7.1 Ensure /var/log/audit is a separate partition ---
    [
        'id' => '1.1.2.7.1', 'title' => 'Ensure /var/log/audit is a separate partition', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
if findmnt -kn /var/log/audit >/dev/null; then echo "** PASS **"; else echo "** FAIL **"; fi
BASH
    ],

    // --- 1.1.2.7.2 Ensure nodev option set on /var/log/audit partition ---
    [
        'id' => '1.1.2.7.2', 'title' => 'Ensure nodev option set on /var/log/audit partition', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output=()
    a_output2=()
    a_output_info=()

    if findmnt -kn /var/log/audit >/dev/null 2>&1; then
        a_output+=("  - /var/log/audit is a separate mount point.")
        a_output_info+=("  - Mount details: $(findmnt -kn /var/log/audit)")

        if findmnt -kn /var/log/audit | grep -q 'nodev'; then
            a_output+=("  - /var/log/audit is mounted with the 'nodev' option.")
        else
            a_output2+=("  - /var/log/audit is NOT mounted with the 'nodev' option.")
        fi
    else
        a_output2+=("  - /var/log/audit is NOT a separate mount point.")
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output2[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
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

    // --- 1.1.2.7.3 Ensure nosuid option set on /var/log/audit partition ---
    [
        'id' => '1.1.2.7.3', 'title' => 'Ensure nosuid option set on /var/log/audit partition', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output=()
    a_output2=()
    a_output_info=()

    if findmnt -kn /var/log/audit >/dev/null 2>&1; then
        a_output+=("  - /var/log/audit is a separate mount point.")
        a_output_info+=("  - Mount details: $(findmnt -kn /var/log/audit)")

        if findmnt -kn /var/log/audit | grep -q 'nosuid'; then
            a_output+=("  - /var/log/audit is mounted with the 'nosuid' option.")
        else
            a_output2+=("  - /var/log/audit is NOT mounted with the 'nosuid' option.")
        fi
    else
        a_output2+=("  - /var/log/audit is NOT a separate mount point.")
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi
    if [ "${#a_output2[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
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

    // --- 1.1.2.7.4 Ensure noexec option set on /var/log/audit partition ---
    [
        'id' => '1.1.2.7.4', 'title' => 'Ensure noexec option set on /var/log/audit partition', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output=()
    a_output2=()
    a_output_info=()

    if findmnt -kn /var/log/audit >/dev/null 2>&1; then
        a_output+=("  - /var/log/audit is a separate mount point.")
        a_output_info+=("  - Mount details: $(findmnt -kn /var/log/audit)")

        if findmnt -kn /var/log/audit | grep -q 'noexec'; then
            a_output+=("  - /var/log/audit is mounted with the 'noexec' option.")
        else
            a_output2+=("  - /var/log/audit is NOT mounted with the 'noexec' option.")
        fi
    else
        a_output2+=("  - /var/log/audit is NOT a separate mount point.")
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output2[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
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

    // --- 1.2 Package Management ---
    [ 'id' => '1.2', 'title' => 'Package Management', 'type' => 'header' ],

    // --- 1.2.1 Configure Package Repositories ---
    [ 'id' => '1.2.2', 'title' => 'Configure Package Repositories', 'type' => 'header' ],


    // --- 1.2.1.1 Ensure GPG keys are configured ---
    [
        'id' => '1.2.1.1', 'title' => 'Ensure GPG keys are configured', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
apt-key list
BASH
    ],

    // --- 1.2.1.2 Ensure package manager repositories are configured ---
    [
        'id' => '1.2.1.2', 'title' => 'Ensure package manager repositories are configured', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
apt-cache policy
BASH
    ],

    // --- 1.2.2 Configure Package Updates ---
    [ 'id' => '1.2.2', 'title' => 'Configure Package Updates', 'type' => 'header' ],

    // --- 1.2.2.1 Ensure updates, patches, and additional security software are installed ---
    [
        'id' => '1.2.2.1', 'title' => 'Ensure updates, patches, and additional security software are installed', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    printf '%s\n' "This is a manual check. Please run the following commands to verify:"
    printf '%s\n' "# apt update"
    printf '%s\n' "# apt upgrade"
    printf '\n%s\n' "If there are updates available, please install them according to your site policy."
    printf '\n%s\n' "** REVIEW ** Required: Manually review the output of the commands above."
}
BASH
    ],
    // --- 1.3 Mandatory Access Control ---
    [ 'id' => '1.3', 'title' => 'Mandatory Access Control', 'type' => 'header' ],

    // --- 1.3.1 Configure AppArmor ---
    [ 'id' => '1.3.1', 'title' => 'Configure AppArmor', 'type' => 'header' ],

    // --- 1.3.1.1 Ensure AppArmor is installed ---
    [
        'id' => '1.3.1.1', 'title' => 'Ensure AppArmor is installed', 'profile' => 'Level 1', 'type' => 'Automated', 'requires_root' => false,
        'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output=()
    a_output2=()

    if dpkg-query -s apparmor &>/dev/null; then
        a_output+=("  - Package 'apparmor' is installed.")
    else
        a_output2+=("  - Package 'apparmor' is NOT installed.")
    fi

    if dpkg-query -s apparmor-utils &>/dev/null; then
        a_output+=("  - Package 'apparmor-utils' is installed.")
    else
        a_output2+=("  - Package 'apparmor-utils' is NOT installed.")
    fi

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
    // 1.3.1.2 Ensure AppArmor is enabled in the bootloader configuration
    [
        'id' => '1.3.1.2', 'title' => 'Ensure AppArmor is enabled in the bootloader configuration', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => true, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output=()
    a_output2=()

    output_apparmor=$(grep "^\s*linux" /boot/grub/grub.cfg 2>/dev/null | grep -v "apparmor=1")

    if [ -z "$output_apparmor" ]; then
        a_output+=("  - All boot entries contain 'apparmor=1'.")
    else
        a_output2+=("  - Not all boot entries contain 'apparmor=1'.")
    fi

    output_security=$(grep "^\s*linux" /boot/grub/grub.cfg 2>/dev/null | grep -v "security=apparmor")

    if [ -z "$output_security" ]; then
        a_output+=("  - All boot entries contain 'security=apparmor'.")
    else
        a_output2+=("  - Not all boot entries contain 'security=apparmor'.")
    fi

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

    // --- 1.3.1.3 Ensure all AppArmor Profiles are in enforce or complain mode ---
    [
        'id' => '1.3.1.3', 'title' => 'Ensure all AppArmor Profiles are in enforce or complain mode', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => true, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output=()
    a_output2=()
    a_output_info=()

    APPARMOR_STATUS=$(apparmor_status 2>/dev/null)

    if [ -z "$APPARMOR_STATUS" ]; then
        a_output2+=("  - Could not get AppArmor status. 'apparmor-utils' may not be installed or the service may not be running.")
    else
        a_output_info+=("  - Full apparmor_status output:")
        while IFS= read -r line; do
            a_output_info+=("    $line")
        done <<< "$APPARMOR_STATUS"

        if echo "$APPARMOR_STATUS" | grep "profiles are loaded" | grep -q "0 profiles are loaded"; then
            a_output2+=("  - No AppArmor profiles are loaded.")
        else
            a_output+=("  - AppArmor profiles are loaded and in enforce or complain mode.")
        fi

        unconfined_line=$(echo "$APPARMOR_STATUS" | grep "processes are unconfined")
        if [ -n "$unconfined_line" ]; then
            unconfined_count=$(echo "$unconfined_line" | awk '{print $1}')
            if [ "$unconfined_count" -gt 0 ]; then
                a_output2+=("  - Found $unconfined_count unconfined process(es).")
            else
                a_output+=("  - No processes are unconfined.")
            fi
        else
             a_output+=("  - No processes are unconfined.")
        fi
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output2[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
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
    //--- 1.3.1.4 Ensure all AppArmor Profiles are enforcing ---
    [
        'id' => '1.3.1.4', 'title' => 'Ensure all AppArmor Profiles are enforcing', 'profile' => 'Level 2', 'type' => 'Automated',
        'requires_root' => true, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output=()
    a_output2=()
    a_output_info=()

    APPARMOR_STATUS=$(apparmor_status 2>/dev/null)

    if [ -z "$APPARMOR_STATUS" ]; then
        a_output2+=("  - Could not get AppArmor status. 'apparmor-utils' may not be installed or the service may not be running.")
    else
        a_output_info+=("  - Full apparmor_status output:")
        while IFS= read -r line; do
            a_output_info+=("    $line")
        done <<< "$APPARMOR_STATUS"

        complain_line=$(echo "$APPARMOR_STATUS" | grep "profiles are in complain mode")
        if [ -n "$complain_line" ]; then
            complain_count=$(echo "$complain_line" | awk '{print $1}')
            if [ "$complain_count" -gt 0 ]; then
                a_output2+=("  - Found $complain_count profiles in complain mode.")
            else
                a_output+=("  - All profiles are in enforce mode (0 profiles in complain mode).")
            fi
        else
            a_output+=("  - All profiles are in enforce mode.")
        fi

        unconfined_line=$(echo "$APPARMOR_STATUS" | grep "processes are unconfined")
        if [ -n "$unconfined_line" ]; then
            unconfined_count=$(echo "$unconfined_line" | awk '{print $1}')
            if [ "$unconfined_count" -gt 0 ]; then
                a_output2+=("  - Found $unconfined_count unconfined process(es).")
            else
                a_output+=("  - No processes are unconfined.")
            fi
        else
             a_output+=("  - No processes are unconfined.")
        fi
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output2[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
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

    // --- 1.4 Configure Bootloader ---
    [ 'id' => '1.4', 'title' => 'Configure Bootloader', 'type' => 'header' ],

    // --- 1.4.1 Ensure bootloader password is set ---
    [
        'id' => '1.4.1', 'title' => 'Ensure bootloader password is set', 'profile' => 'Level 1', 'type' => 'Automated', 'requires_root' => true,
        'audit_script' => <<<'BASH'
grep "^set superusers" /boot/grub/grub.cfg
awk -F. '/^\s*password/ {print $1"."$2"."$3}' /boot/grub/grub.cfg
BASH
    ],
    //--- 1.4.2 Ensure access to bootloader config is configured ---
    [
        'id' => '1.4.2', 'title' => 'Ensure access to bootloader config is configured', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output=()
    a_output2=()
    a_output_info=()
    GRUB_CFG="/boot/grub/grub.cfg"

    if [ ! -f "$GRUB_CFG" ]; then
        a_output2+=("  - GRUB config file not found at $GRUB_CFG")
    else
        a_output_info+=("  - Checking file: $GRUB_CFG")
        a_output_info+=("  - Actual state: $(stat -Lc 'Access: (%#a/%A) Uid: (%u/%U) Gid: (%g/%G)' "$GRUB_CFG")")

        PERMS=$(stat -Lc '%#a' "$GRUB_CFG")
        FILE_UID=$(stat -Lc '%u' "$GRUB_CFG")
        FILE_GID=$(stat -Lc '%g' "$GRUB_CFG")

        if [ $(( $PERMS & 0077 )) -eq 0 ]; then
            a_output+=("  - Permissions ('$PERMS') are 600 or more restrictive.")
        else
            a_output2+=("  - Permissions ('$PERMS') are NOT 600 or more restrictive.")
        fi

        if [ "$FILE_UID" -eq 0 ]; then
            a_output+=("  - Owner is 'root' (UID 0).")
        else
            a_output2+=("  - Owner is not 'root' (UID is $FILE_UID).")
        fi

        if [ "$FILE_GID" -eq 0 ]; then
            a_output+=("  - Group is 'root' (GID 0).")
        else
            a_output2+=("  - Group is not 'root' (GID is $FILE_GID).")
        fi
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output2[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
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

    // --- 1.5 Additional Process Hardening ---
    [ 'id' => '1.5', 'title' => 'Additional Process Hardening', 'type' => 'header' ],

    // --- 1.5.1 Ensure address space layout randomization is enabled ---
[
    'id' => '1.5.1', 'title' => 'Ensure address space layout randomization is enabled', 'profile' => 'Level 1', 'type' => 'Automated',
    'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
   a_output=()
   a_output2=()
   a_parlist=(kernel.randomize_va_space=2)

   l_ufwscf="$([ -f /etc/default/ufw ] && awk -F= '/^\s*IPT_SYSCTL=/ {print $2}' /etc/default/ufw)"

   f_kernel_parameter_chk() {
      # Check current value
      l_running_parameter_value="$(sysctl "$l_parameter_name" 2>/dev/null | awk -F= '{print $2}' | xargs)"
      if grep -Pq -- "\b$l_parameter_value\b" <<< "$l_running_parameter_value"; then
         a_output+=(
            " - \"$l_parameter_name\" is correctly set to \"$l_running_parameter_value\""
            "    in the running configuration"
         )
      else
         a_output2+=(
            " - \"$l_parameter_name\" is incorrectly set to \"$l_running_parameter_value\""
            "    in the running configuration"
            "    and should have a value of: \"$l_value_out\""
         )
      fi

      # Check persistent config files
      unset A_out
      declare -A A_out

      while read -r l_out; do
         if [ -n "$l_out" ]; then
            if [[ "$l_out" =~ ^\s*# ]]; then
               l_file="${l_out//# /}"
            else
               l_kpar="$(awk -F= '{print $1}' <<< "$l_out" | xargs)"
               [ "$l_kpar" = "$l_parameter_name" ] && A_out["$l_kpar"]="$l_file"
            fi
         fi
      done < <("$l_systemdsysctl" --cat-config | grep -Po '^\h*([^#\n\r]+|#\h*/[^#\n\r\h]+\.conf\b)')

      # UFW support
      if [ -n "$l_ufwscf" ]; then
         l_kpar="$(grep -Po "^\h*$l_parameter_name\b" "$l_ufwscf" | xargs)"
         l_kpar="${l_kpar//\//.}"
         [ "$l_kpar" = "$l_parameter_name" ] && A_out["$l_kpar"]="$l_ufwscf"
      fi

      # Evaluate config file settings
      if (( ${#A_out[@]} > 0 )); then
         while IFS="=" read -r l_fkpname l_file_parameter_value; do
            l_fkpname="${l_fkpname// /}"
            l_file_parameter_value="${l_file_parameter_value// /}"

            if grep -Pq -- "\b$l_parameter_value\b" <<< "$l_file_parameter_value"; then
               a_output+=(
                  " - \"$l_parameter_name\" is correctly set to \"$l_file_parameter_value\""
                  "    in \"$(printf '%s' "${A_out[@]}")\""
               )
            else
               a_output2+=(
                  " - \"$l_parameter_name\" is incorrectly set to \"$l_file_parameter_value\""
                  "    in \"$(printf '%s' "${A_out[@]}")\""
                  "    and should have a value of: \"$l_value_out\""
               )
            fi
         done < <(grep -Pho -- "^\h*$l_parameter_name\h*=\h*\H+" "${A_out[@]}")
      else
         a_output2+=(
            " - \"$l_parameter_name\" is not set in an included file"
            "    ** Note: \"$l_parameter_name\" may be set in a file that's ignored by load procedure **"
         )
      fi
   }

   l_systemdsysctl="$(readlink -f /lib/systemd/systemd-sysctl)"

   while IFS="=" read -r l_parameter_name l_parameter_value; do
      l_parameter_name="${l_parameter_name// /}"
      l_parameter_value="${l_parameter_value// /}"
      l_value_out="${l_parameter_value//-/ through }"
      l_value_out="${l_value_out//|/ or }"
      l_value_out="$(tr -d '(){}' <<< "$l_value_out")"

      f_kernel_parameter_chk
   done < <(printf '%s\n' "${a_parlist[@]}")

   echo ""
   echo "- Audit Result:"
   if [ "${#a_output2[@]}" -le 0 ]; then
      echo "  ** PASS **"
      printf '%s\n' "${a_output[@]}"
   else
      echo "  ** FAIL **"
      echo " - Reason(s) for audit failure:"
      printf '%s\n' "${a_output2[@]}"
      if [ "${#a_output[@]}" -gt 0 ]; then
         echo ""
         echo "- Correctly set:"
         printf '%s\n' "${a_output[@]}"
      fi
   fi
}
BASH
    ],

    // --- 1.5.2 Ensure ptrace_scope is restricted ---
    [
        'id' => '1.5.2', 'title' => 'Ensure ptrace_scope is restricted', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
   a_output=()
   a_output2=()
   a_parlist=("kernel.yama.ptrace_scope=(1|2|3)")

   l_ufwscf="$([ -f /etc/default/ufw ] && awk -F= '/^\s*IPT_SYSCTL=/ {print $2}' /etc/default/ufw)"

   f_kernel_parameter_chk() {
      # Check running configuration
      l_running_parameter_value="$(sysctl "$l_parameter_name" 2>/dev/null | awk -F= '{print $2}' | xargs)"
      if grep -Pq -- "\b$l_parameter_value\b" <<< "$l_running_parameter_value"; then
         a_output+=(
            " - \"$l_parameter_name\" is correctly set to \"$l_running_parameter_value\""
            "    in the running configuration"
         )
      else
         a_output2+=(
            " - \"$l_parameter_name\" is incorrectly set to \"$l_running_parameter_value\""
            "    in the running configuration"
            "    and should have a value of: \"$l_value_out\""
         )
      fi

      # Check durable setting (files)
      unset A_out
      declare -A A_out

      while read -r l_out; do
         if [ -n "$l_out" ]; then
            if [[ "$l_out" =~ ^\s*# ]]; then
               l_file="${l_out//# /}"
            else
               l_kpar="$(awk -F= '{print $1}' <<< "$l_out" | xargs)"
               [ "$l_kpar" = "$l_parameter_name" ] && A_out["$l_kpar"]="$l_file"
            fi
         fi
      done < <("$l_systemdsysctl" --cat-config | grep -Po '^\h*([^#\n\r]+|#\h*/[^#\n\r\h]+\.conf\b)')

      # Account for UFW sysctl setting
      if [ -n "$l_ufwscf" ]; then
         l_kpar="$(grep -Po "^\h*$l_parameter_name\b" "$l_ufwscf" | xargs)"
         l_kpar="${l_kpar//\//.}"
         [ "$l_kpar" = "$l_parameter_name" ] && A_out["$l_kpar"]="$l_ufwscf"
      fi

      if (( ${#A_out[@]} > 0 )); then
         while IFS="=" read -r l_fkpname l_file_parameter_value; do
            l_fkpname="${l_fkpname// /}"
            l_file_parameter_value="${l_file_parameter_value// /}"

            if grep -Pq -- "\b$l_parameter_value\b" <<< "$l_file_parameter_value"; then
               a_output+=(
                  " - \"$l_parameter_name\" is correctly set to \"$l_file_parameter_value\""
                  "    in \"$(printf '%s' "${A_out[@]}")\""
               )
            else
               a_output2+=(
                  " - \"$l_parameter_name\" is incorrectly set to \"$l_file_parameter_value\""
                  "    in \"$(printf '%s' "${A_out[@]}")\""
                  "    and should have a value of: \"$l_value_out\""
               )
            fi
         done < <(grep -Po -- "^\h*$l_parameter_name\h*=\h*\H+" "${A_out[@]}")
      else
         a_output2+=(
            " - \"$l_parameter_name\" is not set in an included file"
            "    ** Note: \"$l_parameter_name\" may be set in a file that's ignored by systemd-sysctl **"
         )
      fi
   }

   l_systemdsysctl="$(readlink -f /lib/systemd/systemd-sysctl)"

   while IFS="=" read -r l_parameter_name l_parameter_value; do
      l_parameter_name="${l_parameter_name// /}"
      l_parameter_value="${l_parameter_value// /}"
      l_value_out="${l_parameter_value//-/ through }"
      l_value_out="${l_value_out//|/ or }"
      l_value_out="$(tr -d '(){}' <<< "$l_value_out")"

      f_kernel_parameter_chk
   done < <(printf '%s\n' "${a_parlist[@]}")

   echo ""
   echo "- Audit Result:"
   if [ "${#a_output2[@]}" -le 0 ]; then
      echo "  ** PASS **"
      printf '%s\n' "${a_output[@]}"
   else
      echo "  ** FAIL **"
      echo " - Reason(s) for audit failure:"
      printf '%s\n' "${a_output2[@]}"
      if [ "${#a_output[@]}" -gt 0 ]; then
         echo ""
         echo "- Correctly set:"
         printf '%s\n' "${a_output[@]}"
      fi
   fi
}
BASH
    ],

    // --- 1.5.3 Ensure core dumps are restricted ---
    [
        'id' => '1.5.3', 'title' => 'Ensure core dumps are restricted', 'profile' => 'Level 1', 'type' => 'Automated', 'requires_root' => false,
        'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
   a_output=()
   a_output2=()
   a_parlist=("fs.suid_dumpable=0")

   l_ufwscf="$([ -f /etc/default/ufw ] && awk -F= '/^\s*IPT_SYSCTL=/ {print $2}' /etc/default/ufw)"

   f_kernel_parameter_chk() {
      # Check running configuration
      l_running_parameter_value="$(sysctl "$l_parameter_name" 2>/dev/null | awk -F= '{print $2}' | xargs)"
      if grep -Pq -- "\b$l_parameter_value\b" <<< "$l_running_parameter_value"; then
         a_output+=(
            " - \"$l_parameter_name\" is correctly set to \"$l_running_parameter_value\""
            "    in the running configuration"
         )
      else
         a_output2+=(
            " - \"$l_parameter_name\" is incorrectly set to \"$l_running_parameter_value\""
            "    in the running configuration"
            "    and should have a value of: \"$l_value_out\""
         )
      fi

      # Check durable setting (files)
      unset A_out
      declare -A A_out

      while read -r l_out; do
         if [ -n "$l_out" ]; then
            if [[ "$l_out" =~ ^\s*# ]]; then
               l_file="${l_out//# /}"
            else
               l_kpar="$(awk -F= '{print $1}' <<< "$l_out" | xargs)"
               [ "$l_kpar" = "$l_parameter_name" ] && A_out["$l_kpar"]="$l_file"
            fi
         fi
      done < <("$l_systemdsysctl" --cat-config | grep -Po '^\h*([^#\n\r]+|#\h*/[^#\n\r\h]+\.conf\b)')

      # UFW config support
      if [ -n "$l_ufwscf" ]; then
         l_kpar="$(grep -Po "^\h*$l_parameter_name\b" "$l_ufwscf" | xargs)"
         l_kpar="${l_kpar//\//.}"
         [ "$l_kpar" = "$l_parameter_name" ] && A_out["$l_kpar"]="$l_ufwscf"
      fi

      if (( ${#A_out[@]} > 0 )); then
         while IFS="=" read -r l_fkpname l_file_parameter_value; do
            l_fkpname="${l_fkpname// /}"
            l_file_parameter_value="${l_file_parameter_value// /}"

            if grep -Pq -- "\b$l_parameter_value\b" <<< "$l_file_parameter_value"; then
               a_output+=(
                  " - \"$l_parameter_name\" is correctly set to \"$l_file_parameter_value\""
                  "    in \"$(printf '%s' "${A_out[@]}")\""
               )
            else
               a_output2+=(
                  " - \"$l_parameter_name\" is incorrectly set to \"$l_file_parameter_value\""
                  "    in \"$(printf '%s' "${A_out[@]}")\""
                  "    and should have a value of: \"$l_value_out\""
               )
            fi
         done < <(grep -Po -- "^\h*$l_parameter_name\h*=\h*\H+" "${A_out[@]}")
      else
         a_output2+=(
            " - \"$l_parameter_name\" is not set in an included file"
            "    ** Note: \"$l_parameter_name\" may be set in a file that's ignored by systemd-sysctl **"
         )
      fi
   }

   l_systemdsysctl="$(readlink -f /lib/systemd/systemd-sysctl)"

   while IFS="=" read -r l_parameter_name l_parameter_value; do
      l_parameter_name="${l_parameter_name// /}"
      l_parameter_value="${l_parameter_value// /}"
      l_value_out="${l_parameter_value//-/ through }"
      l_value_out="${l_value_out//|/ or }"
      l_value_out="$(tr -d '(){}' <<< "$l_value_out")"

      f_kernel_parameter_chk
   done < <(printf '%s\n' "${a_parlist[@]}")

   echo ""
   echo "- Audit Result:"
   if [ "${#a_output2[@]}" -le 0 ]; then
      echo "  ** PASS **"
      printf '%s\n' "${a_output[@]}"
   else
      echo "  ** FAIL **"
      echo " - Reason(s) for audit failure:"
      printf '%s\n' "${a_output2[@]}"
      if [ "${#a_output[@]}" -gt 0 ]; then
         echo ""
         echo "- Correctly set:"
         printf '%s\n' "${a_output[@]}"
      fi
   fi
}
BASH
    ],

    // --- 1.6 Command Line Warning Banners ---
    [ 'id' => '1.6', 'title' => 'Command Line Warning Banners', 'type' => 'header' ],

    // --- 1.6.1 Ensure message of the day is configured properly ----
    [
        'id' => '1.6.1', 'title' => 'Ensure message of the day is configured properly', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output=()
    a_output2=()
    a_output_info=()
    MOTD_FILE="/etc/motd"

    if [ ! -f "$MOTD_FILE" ]; then
        a_output+=("  - /etc/motd file does not exist, which is a compliant state.")
    else
        a_output_info+=("  - Content of $MOTD_FILE for manual review:")
        a_output_info+=("  -------------------------------------------")
        while IFS= read -r line; do
            a_output_info+=("    $line")
        done < "$MOTD_FILE"
        a_output_info+=("  -------------------------------------------")

        if grep -E -i "(\\\\v|\\\\r|\\\\m|\\\\s)" "$MOTD_FILE" >/dev/null 2>&1; then
            a_output2+=("  - /etc/motd contains prohibited OS-specific macros (e.g. \\v, \\r, \\m, \\s).")
        else
            a_output+=("  - /etc/motd does not contain prohibited OS-specific macros.")
            a_output+=("  - Note: Please manually verify the content against your site policy.")
        fi
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output2[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "${a_output[@]}"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output2[@]}"

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "Edit the /etc/motd file with the appropriate contents according to your site policy,"
        printf '%s\n' "remove any instances of \\m, \\r, \\s, \\v or references to the OS platform."
        printf '%s\n' "- OR - If the motd is not used, this file can be removed:"
        printf '%s\n' "# rm /etc/motd"
    fi
}
BASH
    ],

    // --- 1.6.2 Ensure local login warning banner is configured properly ---
        [
        'id' => '1.6.2', 'title' => 'Ensure local login warning banner is configured properly', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output=()
    a_output2=()
    a_output_info=()
    ISSUE_FILE="/etc/issue"

    if [ ! -f "$ISSUE_FILE" ]; then
        a_output2+=("  - $ISSUE_FILE file not found.")
    else
        a_output_info+=("  - Content of $ISSUE_FILE for manual review:")
        a_output_info+=("  -------------------------------------------")
        while IFS= read -r line; do
            a_output_info+=("    $line")
        done < "$ISSUE_FILE"
        a_output_info+=("  -------------------------------------------")

        OS_ID=$(grep '^ID=' /etc/os-release | cut -d= -f2 | sed -e 's/"//g')
        FORBIDDEN_PATTERN="(\\\\v|\\\\r|\\\\m|\\\\s|$OS_ID)"

        if grep -E -i "$FORBIDDEN_PATTERN" "$ISSUE_FILE" >/dev/null 2>&1; then
            a_output2+=("  - /etc/issue contains prohibited OS-specific information.")
            a_output2+=("    - Found: $(grep -E -i -o "$FORBIDDEN_PATTERN" "$ISSUE_FILE" | tr '\n' ' ')")
        else
            a_output+=("  - /etc/issue does not contain prohibited OS-specific information.")
            a_output+=("  - Note: Please manually verify the content against your site policy.")
        fi
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output2[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "${a_output[@]}"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output2[@]}"

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "Edit the /etc/issue file with the appropriate contents according to your site policy,"
        printf '%s\n' "remove any instances of \\m, \\r, \\s, \\v or references to the OS platform."
        printf '%s\n' "Example:"
        printf '%s\n' "# echo \"Authorized users only. All activity may be monitored and reported.\" > /etc/issue"
    fi
}
BASH
    ],

    // --- 1.6.3 Ensure remote login warning banner is configured properly ---
    [
        'id' => '1.6.3', 'title' => 'Ensure remote login warning banner is configured properly', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output=()
    a_output2=()
    a_output_info=()
    ISSUE_NET_FILE="/etc/issue.net"

    if [ ! -f "$ISSUE_NET_FILE" ]; then
        a_output2+=("  - $ISSUE_NET_FILE file not found.")
    else
        a_output_info+=("  - Content of $ISSUE_NET_FILE for manual review:")
        a_output_info+=("  -------------------------------------------")
        while IFS= read -r line; do
            a_output_info+=("    $line")
        done < "$ISSUE_NET_FILE"
        a_output_info+=("  -------------------------------------------")

        OS_ID=$(grep '^ID=' /etc/os-release | cut -d= -f2 | sed -e 's/"//g')
        FORBIDDEN_PATTERN="(\\\\v|\\\\r|\\\\m|\\\\s|$OS_ID)"

        if grep -E -i "$FORBIDDEN_PATTERN" "$ISSUE_NET_FILE" >/dev/null 2>&1; then
            a_output2+=("  - $ISSUE_NET_FILE contains prohibited OS-specific information.")
            a_output2+=("    - Found: $(grep -E -i -o "$FORBIDDEN_PATTERN" "$ISSUE_NET_FILE" | tr '\n' ' ')")
        else
            a_output+=("  - $ISSUE_NET_FILE does not contain prohibited OS-specific information.")
            a_output+=("  - Note: Please manually verify the content against your site policy.")
        fi
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output2[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "${a_output[@]}"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output2[@]}"

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "Edit the /etc/issue.net file with the appropriate contents according to your site policy,"
        printf '%s\n' "remove any instances of \\m, \\r, \\s, \\v or references to the OS platform."
        printf '%s\n' "Example:"
        printf '%s\n' "# echo \"Authorized users only. All activity may be monitored and reported.\" > /etc/issue.net"
    fi
}
BASH
    ],

    // --- 1.6.4 Ensure access to /etc/motd is configured ---
    [
        'id' => '1.6.4', 'title' => 'Ensure access to /etc/motd is configured', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output=()
    a_output2=()
    a_output_info=()
    MOTD_FILE="/etc/motd"

    if [ ! -f "$MOTD_FILE" ]; then
        a_output+=("  - /etc/motd file does not exist, which is a compliant state.")
    else
        a_output_info+=("  - Checking file: $MOTD_FILE")
        a_output_info+=("  - Actual state: $(stat -Lc 'Access: (%#a/%A) Uid: (%u/%U) Gid: (%g/%G)' "$MOTD_FILE")")

        PERMS=$(stat -Lc '%#a' "$MOTD_FILE")
        FILE_UID=$(stat -Lc '%u' "$MOTD_FILE")
        FILE_GID=$(stat -Lc '%g' "$MOTD_FILE")

        if [ $(( $PERMS & 0033 )) -eq 0 ]; then
            a_output+=("  - Permissions ('$PERMS') are 644 or more restrictive.")
        else
            a_output2+=("  - Permissions ('$PERMS') are NOT 644 or more restrictive.")
        fi

        if [ "$FILE_UID" -eq 0 ]; then
            a_output+=("  - Owner is 'root' (UID 0).")
        else
            a_output2+=("  - Owner is not 'root' (UID is $FILE_UID).")
        fi

        if [ "$FILE_GID" -eq 0 ]; then
            a_output+=("  - Group is 'root' (GID 0).")
        else
            a_output2+=("  - Group is not 'root' (GID is $FILE_GID).")
        fi
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output2[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
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

    // --- 1.6.5 Ensure access to /etc/issue is configured ---
    [
        'id' => '1.6.5', 'title' => 'Ensure access to /etc/issue is configured', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output=()
    a_output2=()
    a_output_info=()
    ISSUE_FILE="/etc/issue"

    if [ ! -f "$ISSUE_FILE" ]; then
        a_output2+=("  - $ISSUE_FILE file not found.")
    else
        a_output_info+=("  - Checking file: $ISSUE_FILE")
        a_output_info+=("  - Actual state: $(stat -Lc 'Access: (%#a/%A) Uid: (%u/%U) Gid: (%g/%G)' "$ISSUE_FILE")")

        PERMS=$(stat -Lc '%#a' "$ISSUE_FILE")
        FILE_UID=$(stat -Lc '%u' "$ISSUE_FILE")
        FILE_GID=$(stat -Lc '%g' "$ISSUE_FILE")

        if [ $(( $PERMS & 0033 )) -eq 0 ]; then
            a_output+=("  - Permissions ('$PERMS') are 644 or more restrictive.")
        else
            a_output2+=("  - Permissions ('$PERMS') are NOT 644 or more restrictive.")
        fi

        if [ "$FILE_UID" -eq 0 ]; then
            a_output+=("  - Owner is 'root' (UID 0).")
        else
            a_output2+=("  - Owner is not 'root' (UID is $FILE_UID).")
        fi

        if [ "$FILE_GID" -eq 0 ]; then
            a_output+=("  - Group is 'root' (GID 0).")
        else
            a_output2+=("  - Group is not 'root' (GID is $FILE_GID).")
        fi
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output2[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
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

    // --- 1.6.6 Ensure access to /etc/issue.net is configured ---
    [
        'id' => '1.6.6', 'title' => 'Ensure access to /etc/issue.net is configured', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output=()
    a_output2=()
    a_output_info=()
    TARGET_FILE="/etc/issue.net"

    if [ ! -f "$TARGET_FILE" ]; then
        a_output2+=("  - $TARGET_FILE file not found.")
    else
        a_output_info+=("  - Checking file: $TARGET_FILE")
        a_output_info+=("  - Actual state: $(stat -Lc 'Access: (%#a/%A) Uid: (%u/%U) Gid: (%g/%G)' "$TARGET_FILE")")

        PERMS=$(stat -Lc '%#a' "$TARGET_FILE")
        FILE_UID=$(stat -Lc '%u' "$TARGET_FILE")
        FILE_GID=$(stat -Lc '%g' "$TARGET_FILE")

        if [ $(( $PERMS & 0033 )) -eq 0 ]; then
            a_output+=("  - Permissions ('$PERMS') are 644 or more restrictive.")
        else
            a_output2+=("  - Permissions ('$PERMS') are NOT 644 or more restrictive.")
        fi

        if [ "$FILE_UID" -eq 0 ]; then
            a_output+=("  - Owner is 'root' (UID 0).")
        else
            a_output2+=("  - Owner is not 'root' (UID is $FILE_UID).")
        fi

        if [ "$FILE_GID" -eq 0 ]; then
            a_output+=("  - Group is 'root' (GID 0).")
        else
            a_output2+=("  - Group is not 'root' (GID is $FILE_GID).")
        fi
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output2[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
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

    // --- 1.7 GNOME Display Manager ---
    [ 'id' => '1.7', 'title' => 'NOME Display Manager', 'type' => 'header' ],
    // - IF - GDM is not installed on the system, this section can be skipped

    // --- 1.7.1 Ensure GDM is removed ---
    [
        'id' => '1.7.1', 'title' => 'Ensure GDM is removed', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output=()
    a_output2=()

    if dpkg-query -s gdm3 &>/dev/null; then
        a_output2+=("  - Package 'gdm3' is installed.")
    else
        a_output+=("  - Package 'gdm3' is not installed.")
    fi

    if [ "${#a_output2[@]}" -le 0 ]; then
        printf '%s\n' "" "- Audit Result:" "  ** PASS **"
        printf '%s\n' "${a_output[@]}"
    else
        printf '%s\n' "" "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output2[@]}"

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "Run the following commands to uninstall gdm3 and remove unused dependencies:"
        printf '%s\n' "# sudo apt purge gdm3"
        printf '%s\n' "# sudo apt autoremove"
    fi
}
BASH
    ],

    // --- 1.7.2 Ensure GDM login banner is configured ---
    [
        'id' => '1.7.2', 'title' => 'Ensure GDM login banner is configured', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output=()
    a_output2=()
    a_output_info=()

    if ! dpkg-query -s gdm3 &>/dev/null; then
        a_output+=("  - GDM is not installed on the system, this check is not applicable.")
    else
        a_output_info+=("  - GDM is installed, proceeding with checks.")

        if ! command -v gsettings &>/dev/null; then
            a_output2+=("  - 'gsettings' command not found. Cannot check GNOME settings.")
        else
            enable_status=$(sudo -u gdm gsettings get org.gnome.login-screen banner-message-enable 2>/dev/null)
            if [ "$enable_status" == "true" ]; then
                a_output+=("  - Banner message is enabled ('true').")
            else
                a_output2+=("  - Banner message is NOT enabled (current value: '$enable_status').")
            fi

            banner_text=$(sudo -u gdm gsettings get org.gnome.login-screen banner-message-text 2>/dev/null)
            a_output_info+=("  - Current banner message text: $banner_text")

            if [ -n "$banner_text" ] && [ "$banner_text" != "''" ]; then
                a_output+=("  - Banner message text is set.")
                a_output+=("  - Note: Please manually verify the text against your site policy.")
            else
                a_output2+=("  - Banner message text is not set or is empty.")
            fi
        fi
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output2[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "${a_output[@]}"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output2[@]}"

        if [ "${#a_output[@]}" -gt 0 ]; then
            printf '\n%s\n' "- Correctly set:"
            printf '%s\n' "${a_output[@]}"
        fi

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "Run the following commands to set and enable the text banner message:"
        printf '%s\n' "# sudo gsettings set org.gnome.login-screen banner-message-text 'Authorized uses only. All activity may be monitored and reported'"
        printf '%s\n' "# sudo gsettings set org.gnome.login-screen banner-message-enable true"
    fi
}
BASH
    ],

    // --- 1.7.3 Ensure GDM disable-user-list option is enabled ---
    [
        'id' => '1.7.3', 'title' => 'Ensure GDM disable-user-list option is enabled', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output=()
    a_output2=()
    a_output_info=()

    if ! dpkg-query -s gdm3 &>/dev/null; then
        a_output+=("  - GDM is not installed on the system, this check is not applicable.")
    else

        a_output_info+=("  - GDM is installed, proceeding with check.")

        if ! command -v gsettings &>/dev/null; then
            a_output2+=("  - 'gsettings' command not found. Cannot check GNOME settings.")
        else

            setting_value=$(sudo -u gdm gsettings get org.gnome.login-screen disable-user-list 2>/dev/null)
            a_output_info+=("  - Current value for disable-user-list: $setting_value")

            if [ "$setting_value" == "true" ]; then
                a_output+=("  - The disable-user-list option is correctly enabled ('true').")
            else
                a_output2+=("  - The disable-user-list option is NOT enabled (current value: '$setting_value').")
            fi
        fi
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output2[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "${a_output[@]}"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output2[@]}"

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "Run the following command to enable the disable-user-list option:"
        printf '%s\n' "# sudo gsettings set org.gnome.login-screen disable-user-list true"
    fi
}
BASH
    ],

    // --- 1.7.4 Ensure GDM screen locks when the user is idle ---
    [
        'id' => '1.7.4', 'title' => 'Ensure GDM screen locks when the user is idle', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    a_output_info=()
    a_output_suggest=()

    RECOMMENDED_LOCK_DELAY=5
    RECOMMENDED_IDLE_DELAY=900

    if ! dpkg-query -s gdm3 &>/dev/null; then
        a_output_pass+=("  - GDM is not installed on the system, this check is not applicable.")
    else
        a_output_info+=("  - GDM is installed, proceeding with checks.")

        if ! command -v gsettings &>/dev/null; then
            a_output_fail+=("  - 'gsettings' command not found. Cannot check GNOME settings.")
        else
            lock_delay_value=$(gsettings get org.gnome.desktop.screensaver lock-delay 2>/dev/null | awk '{print $2}')
            a_output_info+=("  - Current lock-delay value: $lock_delay_value seconds (Recommended: $RECOMMENDED_LOCK_DELAY)")

            if [ -n "$lock_delay_value" ] && [ "$lock_delay_value" -gt 0 ] && [ "$lock_delay_value" -le 5 ]; then
                a_output_pass+=("  - Screen lock delay is compliant (enabled and <= 5 seconds).")
                if [ "$lock_delay_value" -ne "$RECOMMENDED_LOCK_DELAY" ]; then
                    a_output_suggest+=("  - For optimal compliance, consider setting lock-delay to the recommended value of '$RECOMMENDED_LOCK_DELAY'.")
                fi
            else
                a_output_fail+=("  - Screen lock delay is '$lock_delay_value'. It should be > 0 and <= 5.")
            fi

            idle_delay_value=$(gsettings get org.gnome.desktop.session idle-delay 2>/dev/null | awk '{print $2}')
            a_output_info+=("  - Current idle-delay value: $idle_delay_value seconds (Recommended: $RECOMMENDED_IDLE_DELAY)")

            if [ -n "$idle_delay_value" ] && [ "$idle_delay_value" -gt 0 ] && [ "$idle_delay_value" -le 900 ]; then
                a_output_pass+=("  - Session idle delay is compliant (enabled and <= 900 seconds).")
                if [ "$idle_delay_value" -ne "$RECOMMENDED_IDLE_DELAY" ]; then
                    a_output_suggest+=("  - For optimal compliance, consider setting idle-delay to the recommended value of '$RECOMMENDED_IDLE_DELAY'.")
                fi
            else
                a_output_fail+=("  - Session idle delay is '$idle_delay_value'. It should be > 0 and <= 900.")
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
    fi

    if [ "${#a_output_fail[@]}" -gt 0 ] || [ "${#a_output_suggest[@]}" -gt 0 ]; then
         printf '\n\n%s\n' "-- Suggestion --"
         if [ "${#a_output_suggest[@]}" -gt 0 ]; then
             printf '%s\n' "${a_output_suggest[@]}"
         fi
         printf '%s\n' "To apply the recommended CIS settings, run:"
         printf '%s\n' "# gsettings set org.gnome.desktop.screensaver lock-delay $RECOMMENDED_LOCK_DELAY"
         printf '%s\n' "# gsettings set org.gnome.desktop.session idle-delay $RECOMMENDED_IDLE_DELAY"
    fi
}
BASH
    ],

    // --- 1.7.5 Ensure GDM screen locks cannot be overridden ---
    [
        'id' => '1.7.5', 'title' => 'Ensure GDM screen locks cannot be overridden', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
   a_output=()
   a_output2=()

   f_check_setting() {
      if grep -Psrilq -- "^\h*$2\b" /etc/dconf/db/local.d/locks/* 2>/dev/null; then
         echo "- \"$3\" is locked"
      else
         echo "- \"$3\" is not locked or not set"
      fi
   }

   declare -A settings=(
      ["idle-delay"]="/org/gnome/desktop/session/idle-delay"
      ["lock-delay"]="/org/gnome/desktop/screensaver/lock-delay"
   )

   for setting in "${!settings[@]}"; do
      result=$(f_check_setting "$setting" "${settings[$setting]}" "$setting")
      if [[ "$result" == *"not locked"* || "$result" == *"not set"* ]]; then
         a_output2+=("$result")
      else
         a_output+=("$result")
      fi
   done

   echo ""
   echo "- Audit Result:"
   if [ "${#a_output2[@]}" -gt 0 ]; then
      echo "  ** FAIL **"
      echo " - Reason(s) for audit failure:"
      printf '%s\n' "${a_output2[@]}"
      if [ "${#a_output[@]}" -gt 0 ]; then
         echo ""
         echo "- Correctly set:"
         printf '%s\n' "${a_output[@]}"
      fi
   else
      echo "  ** PASS **"
      printf '%s\n' "${a_output[@]}"
   fi
}
BASH
    ],

    // --- 1.7.6 Ensure GDM automatic mounting of removable media is disabled ---
    [
        'id' => '1.7.6', 'title' => 'Ensure GDM automatic mounting of removable media is disabled', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    a_output_info=()

    if ! dpkg-query -s gdm3 &>/dev/null; then
        a_output_pass+=("  - GDM is not installed on the system, this check is not applicable.")
    else
        a_output_info+=("  - GDM is installed, proceeding with checks.")

        if ! command -v gsettings &>/dev/null; then
            a_output_fail+=("  - 'gsettings' command not found. Cannot check GNOME settings.")
        else
            automount_status=$(gsettings get org.gnome.desktop.media-handling automount 2>/dev/null)
            a_output_info+=("  - Current automount status: $automount_status")

            if [ "$automount_status" == "false" ]; then
                a_output_pass+=("  - Automatic mounting is correctly disabled ('false').")
            else
                a_output_fail+=("  - Automatic mounting is NOT disabled (current value: '$automount_status').")
            fi

            automount_open_status=$(gsettings get org.gnome.desktop.media-handling automount-open 2>/dev/null)
            a_output_info+=("  - Current automount-open status: $automount_open_status")

            if [ "$automount_open_status" == "false" ]; then
                a_output_pass+=("  - Opening of automounted media is correctly disabled ('false').")
            else
                a_output_fail+=("  - Opening of automounted media is NOT disabled (current value: '$automount_open_status').")
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

        printf '\n\n%s\n' "-- Remediation --"
        printf '%s\n' "Run the following commands to disable automatic mounting:"
        printf '%s\n' "# gsettings set org.gnome.desktop.media-handling automount false"
        printf '%s\n' "# gsettings set org.gnome.desktop.media-handling automount-open false"
    fi
}
BASH
    ],

    // --- 1.7.7 Ensure GDM disabling automatic mounting of removable media is not overridden ---
    [
        'id' => '1.7.7', 'title' => 'Ensure GDM disabling automatic mounting of removable media is not overridden', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
   a_output=()
   a_output2=()

   check_setting() {
      if grep -Psrilq "^\h*$1\s*=\s*false\b" /etc/dconf/db/local.d/locks/* 2>/dev/null; then
         echo "- \"$3\" is locked and set to false"
      else
         echo "- \"$3\" is not locked or not set to false"
      fi
   }

   declare -A settings=(
      ["automount"]="org/gnome/desktop/media-handling"
      ["automount-open"]="org/gnome/desktop/media-handling"
   )

   for setting in "${!settings[@]}"; do
      result=$(check_setting "$setting" "${settings[$setting]}" "$setting")
      if [[ "$result" == *"not locked"* || "$result" == *"not set to false"* ]]; then
         a_output2+=("$result")
      else
         a_output+=("$result")
      fi
   done

   echo ""
   echo "- Audit Result:"
   if [ "${#a_output2[@]}" -gt 0 ]; then
      echo "  ** FAIL **"
      echo " - Reason(s) for audit failure:"
      printf '%s\n' "${a_output2[@]}"
      if [ "${#a_output[@]}" -gt 0 ]; then
         echo ""
         echo "- Correctly set:"
         printf '%s\n' "${a_output[@]}"
      fi
   else
      echo "  ** PASS **"
      printf '%s\n' "${a_output[@]}"
   fi
}
BASH
    ],

    // --- 1.7.8 Ensure GDM autorun-never is enabled ---
    [
        'id' => '1.7.8', 'title' => 'Ensure GDM autorun-never is enabled', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    a_output_info=()

    if ! dpkg-query -s gdm3 &>/dev/null; then
        a_output_pass+=("  - GDM is not installed on the system, this check is not applicable.")
    else
        a_output_info+=("  - GDM is installed, proceeding with check.")

        if ! command -v gsettings &>/dev/null; then
            a_output_fail+=("  - 'gsettings' command not found. Cannot check GNOME settings.")
        else
            setting_value=$(gsettings get org.gnome.desktop.media-handling autorun-never 2>/dev/null)
            a_output_info+=("  - Current autorun-never status: $setting_value")

            if [ "$setting_value" == "true" ]; then
                a_output_pass+=("  - Autorun is correctly disabled ('true').")
            else
                a_output_fail+=("  - Autorun is NOT disabled (current value: '$setting_value').")
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
        printf '%s\n' "Run the following command to disable autorun:"
        printf '%s\n' "# gsettings set org.gnome.desktop.media-handling autorun-never true"
    fi
}
BASH
    ],

    // --- 1.7.9 Ensure GDM autorun-never is not overridden ---
    [
        'id' => '1.7.9', 'title' => 'Ensure GDM autorun-never is not overridden', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
    # Function to check and report if a specific setting is locked and set to true
    check_setting() {
        local key="$1"
        local path="$2"
        local display_name="$3"
        if grep -Psrilq "^\h*${key}\h*=\h*true\b" /etc/dconf/db/local.d/locks/* 2>/dev/null; then
            echo "- \"$display_name\" is locked and set to true"
        else
            echo "- \"$display_name\" is not locked or not set to true"
        fi
    }

    # Array of settings to check
    declare -A settings=(
        ["autorun-never"]="org/gnome/desktop/media-handling"
    )

    # Output arrays
    l_output=()
    l_output2=()

    # Check each setting
    for setting in "${!settings[@]}"; do
        result=$(check_setting "$setting" "${settings[$setting]}" "$setting")
        l_output+=("$result")
        if [[ "$result" == *"not locked"* || "$result" == *"not set to true"* ]]; then
            l_output2+=("$result")
        fi
    done

    # Report results
    echo "- Audit Result:"
    if [ ${#l_output2[@]} -ne 0 ]; then
        echo "  ** FAIL **"
        echo "- Reason(s) for audit failure:"
        printf '%s\n' "${l_output2[@]}"
    else
        echo "  ** PASS **"
        printf '%s\n' "${l_output[@]}"
    fi
}
BASH
    ],

    // --- 1.7.10 Ensure XDMCP is not enabled ---
    [
        'id' => '1.7.10', 'title' => 'Ensure XDMCP is not enabled', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    a_output_info=()

    config_files=$(grep -Psil -- '^\h*\[xdmcp\]' /etc/gdm3/custom.conf /etc/gdm/custom.conf /etc/gdm3/daemon.conf /etc/gdm/daemon.conf 2>/dev/null)

    if [ -z "$config_files" ]; then
        a_output_pass+=("  - No GDM configuration file with an [xdmcp] section found.")
    else
        a_output_info+=("  - Found GDM config files to check:")
        while IFS= read -r file; do
             a_output_info+=("    - $file")
        done <<< "$config_files"

        check_output=$(while IFS= read -r l_file; do
            awk '/\[xdmcp\]/{ f = 1;next } /\[/{ f = 0 } f {if (/^\s*Enable\s*=\s*true/) print "The file: \"'"$l_file"'\" includes: \"" $0 "\""}' "$l_file"
        done <<< "$config_files")

        if [ -z "$check_output" ]; then
            a_output_pass+=("  - XDMCP is not enabled in any found GDM configuration files.")
        else
            a_output_fail+=("  - XDMCP is enabled in one or more GDM configuration files:")
            while IFS= read -r line; do
                a_output_fail+=("    - $line")
            done <<< "$check_output"
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

        # --- ส่วนของ Suggestion ---
        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "Edit the identified file(s) and either remove the 'Enable=true' line"
        printf '%s\n' "from the [xdmcp] section or set it to 'Enable=false'."
    fi
}
BASH
    ],

/*
    // --- 2 Services ---
    [ 'id' => '2', 'title' => 'Services', 'type' => 'header' ],

    // --- 2.1 Configure Server Services  ---
    [ 'id' => '2.1', 'title' => 'Configure Server Services', 'type' => 'header' ],

    // --- 2.1.1 Ensure autofs services are not in use ---
    [
        'id' => '2.1.1', 'title' => 'Ensure autofs services are not in use', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'

BASH
    ],

    // --- 2.1.2 Ensure avahi daemon services are not in use ---
    [
        'id' => '2.1.2', 'title' => 'Ensure avahi daemon services are not in use', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'

BASH
    ],

    // --- 2.1.3 Ensure dhcp server services are not in use ---
    [
        'id' => '2.1.3', 'title' => 'Ensure dhcp server services are not in use', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'

BASH
    ],

    // --- 2.1.4 Ensure dns server services are not in use ---
    [
        'id' => '2.1.4', 'title' => 'Ensure dns server services are not in use', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'

BASH
    ],

    // --- 2.1.5 Ensure dnsmasq services are not in use ---
    [
        'id' => '2.1.5', 'title' => 'Ensure dnsmasq services are not in use', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'

BASH
    ],

    // --- 2.1.6 Ensure ftp server services are not in use ---
    [
        'id' => '2.1.6', 'title' => 'Ensure ftp server services are not in use', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'

BASH
    ],

    // --- 2.1.7 Ensure ldap server services are not in use ---
    [
        'id' => '2.1.7', 'title' => 'Ensure ldap server services are not in use', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'

BASH
    ],

    // --- 2.1.8 Ensure message access server services are not in use ---
    [
        'id' => '2.1.8', 'title' => 'Ensure message access server services are not in use', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'

BASH
    ],

    // --- 2.1.9 Ensure network file system services are not in use ---
    [
        'id' => '2.1.9', 'title' => 'Ensure network file system services are not in use', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'

BASH
    ],

    // --- 2.1.10 Ensure nis server services are not in use ---
    [
        'id' => '2.1.10', 'title' => 'Ensure nis server services are not in use', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'

BASH
    ],

    // --- 2.1.11 Ensure print server services are not in use ---
    [
        'id' => '2.1.11', 'title' => 'Ensure print server services are not in use', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'

BASH
    ],

    // --- 2.1.12 Ensure rpcbind services are not in use ---
    [
        'id' => '2.1.12', 'title' => 'Ensure rpcbind services are not in use', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'

BASH
    ],

    // --- 2.1.13 Ensure rsync services are not in use ---
    [
        'id' => '2.1.13', 'title' => 'Ensure rsync services are not in use', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'

BASH
    ],

    // --- 2.1.14 Ensure samba file server services are not in use ---
    [
        'id' => '2.1.14', 'title' => 'Ensure samba file server services are not in use', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'

BASH
    ],

    // --- 2.1.15 Ensure snmp services are not in use ---
    [
        'id' => '2.1.15', 'title' => 'Ensure snmp services are not in use', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'

BASH
    ],

    // --- 2.1.16 Ensure tftp server services are not in use ---
    [
        'id' => '2.1.16', 'title' => 'Ensure tftp server services are not in use', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'

BASH
    ],

    // --- 2.1.17 Ensure web proxy server services are not in use ---
    [
        'id' => '2.1.17', 'title' => 'Ensure web proxy server services are not in use', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'

BASH
    ],

    // --- 2.1.18 Ensure web server services are not in use ---
    [
        'id' => '2.1.18', 'title' => 'Ensure web server services are not in use', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'

BASH
    ],

    // --- 2.1.19 Ensure xinetd services are not in use ---
    [
        'id' => '2.1.19', 'title' => 'Ensure xinetd services are not in use', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'

BASH
    ],

    // --- 2.1.20 Ensure X window server services are not in use ---
    [
        'id' => '2.1.20', 'title' => 'Ensure X window server services are not in use', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'

BASH
    ],

    // --- 2.1.21 Ensure mail transfer agent is configured for local-only mode ---
    [
        'id' => '2.1.21', 'title' => 'Ensure mail transfer agent is configured for local-only mode', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'

BASH
    ],

    // --- 2.1.22 Ensure only approved services are listening on a network interface ---
    [
        'id' => '2.1.22', 'title' => 'Ensure only approved services are listening on a network interface', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'

BASH
    ],

    // --- 2.2 Configure Client Services ---
    [ 'id' => '2.2', 'title' => 'Configure Client Services', 'type' => 'header' ],

    // --- 2.2.1 Ensure NIS Client is not installed ---
    [
        'id' => '2.2.1', 'title' => 'Ensure NIS Client is not installed', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'

BASH
    ],

    // --- 2.2.2 Ensure rsh client is not installed ---
    [
        'id' => '2.2.2', 'title' => 'Ensure rsh client is not installed', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'

BASH
    ],

    // --- 2.2.3 Ensure talk client is not installed ---
    [
        'id' => '2.2.3', 'title' => 'Ensure talk client is not installed', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'

BASH
    ],
    // --- 2.2.4 Ensure telnet client is not installed ---
    [
        'id' => '2.2.4', 'title' => 'Ensure telnet client is not installed', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'

BASH
    ],

    // --- 2.2.5 Ensure ldap client is not installed ---
    [
        'id' => '2.2.5', 'title' => 'Ensure ldap client is not installed', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'

BASH
    ],

    // --- 2.2.6 Ensure ftp client is not installed ---
    [
        'id' => '2.2.6', 'title' => 'Ensure ftp client is not installed', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'

BASH
    ],

    // --- 2.3 Configure Time Synchronization ---
    [ 'id' => '2.3', 'title' => 'Configure Time Synchronization', 'type' => 'header' ],

    // --- 2.3.1 Ensure time synchronization is in use  ---
    [ 'id' => '2.3.1', 'title' => 'Ensure time synchronization is in use', 'type' => 'header' ],

    // --- 2.3.1.1 Ensure a single time synchronization daemon is in use ---
    [
        'id' => '2.3.1.1', 'title' => 'Ensure a single time synchronization daemon is in use', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'

BASH
    ],

    // --- 2.3.2 Configure systemd-timesyncd ---
    [ 'id' => '2.3.2', 'title' => 'Configure systemd-timesyncd', 'type' => 'header' ],

    // --- 2.3.2.1 Ensure systemd-timesyncd configured with authorized timeserver ---
    [
        'id' => '2.3.2.1', 'title' => 'Ensure systemd-timesyncd configured with authorized timeserver', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
BASH
    ],

    // --- 2.3.2.2 Ensure systemd-timesyncd is enabled and running ---
    [
        'id' => '2.3.2.2', 'title' => 'Ensure systemd-timesyncd is enabled and running', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
BASH
    ],

    // --- 2.3.3 Configure chrony ---
    [ 'id' => '2.3.3', 'title' => 'Configure chrony', 'type' => 'header' ],

    // --- 2.3.3.1 Ensure chrony is configured with authorized timeserver ---
    [
        'id' => '2.3.3.1', 'title' => 'Ensure chrony is configured with authorized timeserver', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
BASH
    ],

    // --- 2.3.3.2 Ensure chrony is running as user _chrony ---
    [
        'id' => '2.3.3.2', 'title' => 'Ensure chrony is running as user _chrony', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'

BASH
    ],

    // --- 2.3.3.3 Ensure chrony is enabled and running ---
    [
        'id' => '2.3.3.3', 'title' => 'Ensure chrony is enabled and running', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'

BASH
    ],

    // --- 2.4 Job Schedulers ---
    [ 'id' => '2.4', 'title' => 'Job Schedulers', 'type' => 'header' ],

    // --- 2.4.1 Configure cron ---
    [ 'id' => '2.4.1', 'title' => 'Configure cron', 'type' => 'header' ],
    // - IF - cron is not installed on the system, this sub section can be skipped
    
    // --- 2.4.1.1 Ensure cron daemon is enabled and active ---
    [
        'id' => '2.4.1.1', 'title' => 'Ensure cron daemon is enabled and active', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'

BASH
    ],

    // --- 2.4.1.2 Ensure permissions on /etc/crontab are configured ---
    [
        'id' => '2.4.1.2', 'title' => 'Ensure permissions on /etc/crontab are configured', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'

BASH
    ],

    // --- 2.4.1.3 Ensure permissions on /etc/cron.hourly are configured ---
    [
        'id' => '2.4.1.3', 'title' => 'Ensure permissions on /etc/cron.hourly are configured', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'

BASH
    ],

    // --- 2.4.1.4 Ensure permissions on /etc/cron.daily are configured ---
    [
        'id' => '2.4.1.4', 'title' => 'Ensure permissions on /etc/cron.daily are configured', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'

BASH
    ],

    // --- 2.4.1.5 Ensure permissions on /etc/cron.weekly are configured ---
    [
        'id' => '2.4.1.5', 'title' => 'Ensure permissions on /etc/cron.weekly are configured', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'

BASH
    ],

    // --- 2.4.1.6 Ensure permissions on /etc/cron.monthly are configured ---
    [
        'id' => '2.4.1.6', 'title' => 'Ensure permissions on /etc/cron.monthly are configured', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'

BASH
    ],

    // --- 2.4.1.7 Ensure permissions on /etc/cron.d are configured ---
    [
        'id' => '2.4.1.7', 'title' => 'Ensure permissions on /etc/cron.d are configured', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'

BASH
    ],

    // --- 2.4.1.8 Ensure crontab is restricted to authorized users ---
    [
        'id' => '2.4.1.8', 'title' => 'Ensure crontab is restricted to authorized users', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'

BASH
    ],

    // --- 2.4.2 Configure at ---
    [ 'id' => '2.4.2', 'title' => 'Configure at', 'type' => 'header' ],
    //  if at is not installed on the system, this section can be skipped

    // 2.4.2.1 Ensure at is restricted to authorized users
    [
        'id' => '2.4.2.1', 'title' => 'Ensure at is restricted to authorized users', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'

BASH
    ],

    // --- 2.4.2.1 Ensure at is restricted to authorized users ---
    [
        'id' => '2.4.2.1', 'title' => 'Ensure at is restricted to authorized users', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'

BASH
    ],

    // --- 3 Network ---
    [ 'id' => '3', 'title' => 'Network', 'type' => 'header' ],

    // --- 3.1 Configure Network Devices ---
    [ 'id' => '3.1', 'title' => 'Configure Network Devices', 'type' => 'header' ],

    // --- 3.1.1 Ensure IPv6 status is identified ---
    [
        'id' => '3.1.1', 'title' => 'Ensure IPv6 status is identified', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'

BASH
    ],

    // --- 3.1.2 Ensure wireless interfaces are disabled ---
    [
        'id' => '3.1.2', 'title' => 'Ensure wireless interfaces are disabled', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'

BASH
    ],

    // --- 3.1.3 Ensure bluetooth services are not in use ---
    [
        'id' => '3.1.3', 'title' => 'Ensure wireless interfaces are disabled', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'

BASH
    ],

    // --- 3.2 Configure Network Kernel Modules ---
    [ 'id' => '3.2', 'title' => 'Configure Network Kernel Modules', 'type' => 'header' ],

    // --- 3.2.1 Ensure dccp kernel module is not available ---
    [
        'id' => '3.2.1', 'title' => 'Ensure dccp kernel module is not available', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'

BASH
    ],

    // --- 3.2.2 Ensure tipc kernel module is not available ---
    [
        'id' => '3.2.2', 'title' => 'Ensure tipc kernel module is not available', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'

BASH
    ],

    // --- 3.2.3 Ensure rds kernel module is not available ---
    [
        'id' => '3.2.3', 'title' => 'Ensure rds kernel module is not available', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'

BASH
    ],

    // --- 3.2.4 Ensure sctp kernel module is not available ---
    [
        'id' => '3.2.4', 'title' => 'Ensure sctp kernel module is not available', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'

BASH
    ],

    // --- 3.3 Configure Network Kernel Parameters ---
    [ 'id' => '3.3', 'title' => 'Configure Network Kernel Parameters', 'type' => 'header' ],

    // --- 3.3.1 Ensure ip forwarding is disabled ---
    [
        'id' => '3.3.1', 'title' => 'Ensure ip forwarding is disabled', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'

BASH
    ],

    // --- 3.3.2 Ensure packet redirect sending is disabled ---
    [
        'id' => '3.3.2', 'title' => 'Ensure packet redirect sending is disabled', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'

BASH
    ],

    // --- 3.3.3 Ensure bogus icmp responses are ignored ---
    [
        'id' => '3.3.3', 'title' => 'Ensure bogus icmp responses are ignored', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'

BASH
    ],

    // --- 3.3.4 Ensure broadcast icmp requests are ignored ---
    [
        'id' => '3.3.4', 'title' => 'Ensure broadcast icmp requests are ignored', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'

BASH
    ],

    // --- 3.3.5 Ensure icmp redirects are not accepted  ---
    [
        'id' => '3.3.5', 'title' => 'Ensure icmp redirects are not accepted ', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'

BASH
    ],

    // --- 3.3.6 Ensure secure icmp redirects are not accepted  ---
    [
        'id' => '3.3.6', 'title' => 'Ensure secure icmp redirects are not accepted ', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'

BASH
    ],

    // --- 3.3.7 Ensure reverse path filtering is enabled ---
    [
        'id' => '3.3.7', 'title' => 'Ensure reverse path filtering is enabled', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'

BASH
    ],

    // --- 3.3.8 Ensure source routed packets are not accepted ---
    [
        'id' => '3.3.8', 'title' => 'Ensure source routed packets are not accepted', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'

BASH
    ],

    // --- 3.3.9 Ensure suspicious packets are logged ---
    [
        'id' => '3.3.9', 'title' => 'Ensure suspicious packets are logged', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'

BASH
    ],

    // --- 3.3.10 Ensure tcp syn cookies is enabled ---
    [
        'id' => '3.3.10', 'title' => 'Ensure tcp syn cookies is enabled', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'

BASH
    ],

    // --- 3.3.11 Ensure ipv6 router advertisements are not accepted ---
    [
        'id' => '3.3.11', 'title' => 'Ensure ipv6 router advertisements are not accepted', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'

BASH
    ],
*/
];
