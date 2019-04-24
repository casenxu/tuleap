Name:		tuleap-plugin-botmattermost
Version:	@@VERSION@@
Release:	@@RELEASE@@%{?dist}
BuildArch:	noarch
Summary:	Bot Mattermost management for Tuleap

Group:		Development/Tools
License:	GPLv2
URL:		https://enalean.com
Source0:	%{name}-%{version}.tar.gz

BuildRoot:	%{_tmppath}/%{name}-%{version}-%{release}-root
Requires:	tuleap >= 9.11


%description
Bot Mattermost management for Tuleap

%prep
%setup -q


%build

%install
%{__rm} -rf $RPM_BUILD_ROOT

%{__install} -m 755 -d $RPM_BUILD_ROOT/%{_datadir}/tuleap/src/www/assets
%{__install} -m 755 -d $RPM_BUILD_ROOT/%{_datadir}/tuleap/plugins/botmattermost
%{__cp} -ar db include site-content template README.mkd VERSION $RPM_BUILD_ROOT/%{_datadir}/tuleap/plugins/botmattermost
%{__cp} -ar assets $RPM_BUILD_ROOT/%{_datadir}/tuleap/src/www/assets/botmattermost

# www
%{__mkdir} -p $RPM_BUILD_ROOT/%{_datadir}/tuleap/plugins/botmattermost/www
%{__mkdir} -p $RPM_BUILD_ROOT/%{_sysconfdir}/logrotate.d
%{__cp} -ar www/index.php www/admin $RPM_BUILD_ROOT/%{_datadir}/tuleap/plugins/botmattermost/www
%{__cp} -ar %{name}.conf $RPM_BUILD_ROOT/%{_sysconfdir}/logrotate.d

%pre

%clean
%{__rm} -rf $RPM_BUILD_ROOT


%files
%defattr(-,root,root,-)
%{_datadir}/tuleap/plugins/botmattermost
%{_datadir}/tuleap/src/www/assets/botmattermost
%config(noreplace) %{_sysconfdir}/logrotate.d/%{name}.conf


%changelog
* Tue Dec 20 2016 Humbert MOREAUX <humbert.moreaux@enalean.com> -
- First package
