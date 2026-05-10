import { Redirect } from 'expo-router';

export default function Index() {
  const token = (globalThis as any)?.apiToken as string | undefined;

  return <Redirect href={token ? '/screenviews/(tabs)' : '/screenviews/aut-landing/landing-portal'} />;
}
