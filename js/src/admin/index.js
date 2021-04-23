import app from 'flarum/common/app';

app.initializers.add('locoy-publish', () => {
  app.extensionData.for('locoy-publish')
    .registerSetting({
      label: 'locoy-publish.pwd',
      setting: 'locoy-publish.pwd',
      type: 'text',
      help: 'locoy-publish.pwd',
    })
});
