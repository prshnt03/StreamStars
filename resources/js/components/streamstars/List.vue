<template>
  <div class="row">
    <!-- <div class="col-12 mb-2 text-end">
            <router-link :to='{name:"categoryAdd"}' class="btn btn-primary">Create</router-link>
        </div> -->
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h4>Stream List</h4>
        </div>

        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Channel Name</th>
                  <th>Game Name</th>
                  <th>Viewers</th>
                  <th>Started At</th>
                </tr>
              </thead>
              <!--<tbody v-if="arr_streams.data.length > 0">-->
              <tr v-for="(stream, key) in arr_streams.data" :key="key">
                <td>{{ stream.id }}</td>
                <td>
                  {{ stream.channel_name }}
                  <p style="color: black; font-size: 10px">
                    {{ stream.stream_title }}
                  </p>
                </td>
                <td>{{ stream.game_name }}</td>
                <td>{{ stream.viewers_count }}</td>

                <td style="color: blue; font-size: 12px">
                  {{ stream.started_at }}
                </td>

                <!--<td>
                                        <router-link :to='{name:"categoryEdit",params:{id:stream.id}}' class="btn btn-success">Edit</router-link>
                                        <button type="button" @click="deleteStream(stream.id)" class="btn btn-danger">Delete</button>
                                    </td>
                                     -->
              </tr>
              <!-- </tbody>
              <tbody v-else>
                <tr>
                  <td colspan="4" align="center">No Stream Found.</td>
                </tr>
              </tbody> -->
            </table>
          </div>

          <div class="card-footer">
            <pagination
              :data="arr_streams"
              @pagination-change-page="getStreamStars"
            ></pagination>
          </div>

          <!-- <pagination align="center" :data="arr_streams" @pagination-change-page="list"></pagination>
                            -->
        </div>
      </div>
    </div>
<div class="card-footer">
    <pagination
      align="center"
      :data="Object.fromEntries(arr_streams.data)"
      @pagination-change-page="getStreamStars"
    >
      <span slot="prev-nav">Previous </span>
      <span slot="next-nav">Next</span>
    </pagination> </div>

    <br /><br />

    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <div class="card-header">
            <h4>Streams of each Game</h4>
          </div>

          <div class="table-responsive">
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th>Game Name</th>
                  <th>Streamers</th>
                </tr>
              </thead>

              <tbody v-if="arr_game_streamers.length > 0">
                <tr
                  v-for="(gameStreamer, key) in arr_game_streamers"
                  :key="key"
                >
                  <td>{{ gameStreamer.game_name }}</td>
                  <td>{{ gameStreamer.streamers }}</td>
                </tr>
              </tbody>
            </table>
          </div>

          <br />
          <br />
          <div class="card-header">
            <h4>Stream viewers median count</h4>
          </div>

          <div class="table-responsive">
            <p style="color: black; font-size: 12px">Median :</p>
            <tbody v-if="arr_median.length > 0">
              <tr v-for="(mmedian, key) in arr_median" :key="key">
                <td>{{ mmedian.median }}</td>
              </tr>
            </tbody>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import pagination from "laravel-vue-pagination";
export default {
  name: "arr_streams",
  name: "arr_median",
  name: "arr_game_streamers",
  components: {
    pagination,
  },

  data() {
    return {
      arr_streams: {},
      arr_median: [],
      arr_game_streamers: [],
    };
  },
  mounted() {
    this.getStreamStars(), this.getMedianViewers(), this.getGameStreamers();
  },
  methods: {
    async getStreamStars(page = 1) {
      // /api/stream_stars?orderBy=viewers_count&direction=Desc
      if (typeof page === "undefined") {
        page = 1;
      }
      await this.axios
        .get(
          "/api/stream_stars?orderBy=viewers_count&direction=Desc&page=" + page
        )
        .then((response) => {
          console.log(response);
          this.arr_streams = response.data;
          // this.arr_streams.sort(viewers_count)
        })
        .catch((error) => {
          console.log(error);
          this.arr_streams = {};
        });
    }, //api/stream_stars?type=median
    async getMedianViewers() {
      await this.axios
        .get("/api/stream_stars?type=median")
        .then((response) => {
          console.log(response);
          this.arr_median = response.data;
        })
        .catch((error) => {
          console.log(error);
          this.arr_median = [];
        });
    }, //api/stream_stars?type=game_streamers
    async getGameStreamers() {
      await this.axios
        .get("/api/stream_stars?type=game_streamers")
        .then((response) => {
          console.log(response);
          this.arr_game_streamers = response.data;
        })
        .catch((error) => {
          console.log(error);
          this.arr_game_streamers = [];
        });
    },

    deleteStream(id) {
      if (confirm("Are you sure to delete this stream ?")) {
        this.axios
          .delete(`/api/stream_stars/${id}`)
          .then((response) => {
            this.getStreamStars();
          })
          .catch((error) => {
            console.log(error);
          });
      }
    },
  },
};
</script>

<style scoped>
.pagination {
  margin-bottom: 0;
}
</style>