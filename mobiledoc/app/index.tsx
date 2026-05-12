import { Redirect, type Href } from 'expo-router';

export default function Index() {
  const token = (globalThis as any)?.apiToken as string | undefined;
  const user = (globalThis as any)?.currentUser as any | undefined;
  const hasPendingVerification = Boolean((globalThis as any)?.currentUser?.has_pending_verification);

  if (!token) {
    return <Redirect href="/screenviews/aut-landing/landing-portal" />;
  }

  if (!user) {
    return <Redirect href="/screenviews/aut-landing/login-screen" />;
  }

  const role = String(user?.role ?? '').toLowerCase().trim();
  if (role && role !== 'patient') {
    return <Redirect href="/screenviews/aut-landing/staff-account-only" />;
  }

  if (user?.must_change_credentials) {
    return <Redirect href="/screenviews/aut-landing/first-login" />;
  }

  if (user?.is_first_login) {
    if (hasPendingVerification) {
      return <Redirect href="/screenviews/aut-landing/pending-approval" />;
    }
    return <Redirect href={'/screenviews/aut-landing/fillup-info' as Href} />;
  }

  return <Redirect href="/screenviews/(tabs)" />;
}
